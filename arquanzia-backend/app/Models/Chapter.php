<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory, HasUuids;

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
    protected static function booted(): void
    {
        // Le morphisme ne peut pas porter de contrainte de clé étrangère : sans ce nettoyage,
        // les notes d'un texte supprimé resteraient en base sans jamais réapparaître.
        static::deleting(function ($model) {
            $model->authorNotes()->delete();
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
