<?php

namespace App\Models;

use App\Models\Concerns\TracksRevisions;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory, HasUuids, TracksRevisions;

    protected $fillable = [
        'book_id',
        'slug',
        'title',
        'order_index',
        'content_md',
        'is_published',
        'published_at',
        'promo_banner_enabled',
        'promo_banner_text',
        'promo_banner_button_label',
        'promo_banner_button_url',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'promo_banner_enabled' => 'boolean',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ChapterFile::class);
    }

    public function getContentHtmlAttribute(): ?string
    {
        if (! $this->content_md) {
            return null;
        }

        return \App\Helpers\MarkdownHelper::render($this->content_md);
    }

    /** Notes d'autrice ancrées aux paragraphes de ce texte. */
    /** Entrées d'encyclopédie que ce texte cite. */
    public function mentions(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Mention::class, 'source');
    }

    /**
     * Moment où le chapitre est apparu ou a changé pour la dernière fois.
     *
     * `published_at` est saisi à la main dans le formulaire : un chapitre publié sans y toucher
     * le laisse vide, et MySQL range les NULL en dernier — la parution du jour se retrouvait
     * alors derrière de vieux chapitres datés. Le repli sur la création, et la prise en compte
     * des révisions, donnent un classement qui suit le travail réel.
     *
     * Le calcul se fait en PHP, pas en SQL : `GREATEST` existe sous MySQL mais pas sous SQLite,
     * où la suite de tests tourne. Deux régressions sont déjà passées par cette divergence.
     */
    public function lastTouchedAt(): \Illuminate\Support\Carbon
    {
        return collect([$this->published_at, $this->revised_at, $this->created_at])
            ->filter()
            ->sort()
            ->last();
    }

    /** Seule une modification du texte compte comme révision. */
    public function revisableFields(): array
    {
        return ['title', 'content_md'];
    }

    protected static function booted(): void
    {
        // Le morphisme ne peut pas porter de contrainte de clé étrangère : sans ce nettoyage,
        // les notes d'un texte supprimé resteraient en base sans jamais réapparaître.
        static::deleting(function ($model) {
            $model->authorNotes()->delete();
            $model->mentions()->delete();
        });
    }

    public function authorNotes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuthorNote::class, 'notable');
    }

    public function readingTime(): \App\Support\ReadingTime
    {
        return \App\Support\ReadingTime::of($this->content_md);
    }

    public function isComingSoon(): bool
    {
        return ! $this->is_published || ($this->published_at && $this->published_at->isFuture());
    }

    public function scopeVisible($query)
    {
        return $query->where('is_published', true);
    }
}
