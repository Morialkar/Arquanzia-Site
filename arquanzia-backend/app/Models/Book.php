<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'slug',
        'title',
        'author',
        'description_md',
        'cover_media_id',
        'slug_locked_at',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'slug_locked_at' => 'datetime',
    ];

    /**
     * Le slug est gelé dès la première mise en publication : il compose l'URL du livre et de
     * ses chapitres, donc les entrées des flux RSS. Le verrou ne se rouvre jamais, même si le
     * livre est dépublié — sinon dépublier, renommer et republier casserait les abonnés
     * acquis pendant la première publication.
     */
    public function isSlugLocked(): bool
    {
        return $this->slug_locked_at !== null;
    }

    protected static function booted(): void
    {
        // La base efface les chapitres en cascade, ce qui ne déclenche aucun événement de
        // modèle : leurs mentions et leurs notes resteraient alors en base, rattachées à des
        // textes disparus. On supprime donc par les modèles, pour que leurs nettoyages jouent.
        static::deleting(function (Book $book) {
            $book->chapters()->get()->each->delete();
        });
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(PostMedia::class, 'cover_media_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order_index');
    }

    public function files(): HasMany
    {
        return $this->hasMany(BookFile::class);
    }

    public function publishedChapters(): HasMany
    {
        return $this->chapters()->where('is_published', true);
    }

    /** Somme des chapitres publiés : un brouillon n'allonge pas la lecture annoncée. */
    public function readingTime(): \App\Support\ReadingTime
    {
        return \App\Support\ReadingTime::ofMany(
            $this->publishedChapters()->pluck('content_md')
        );
    }

    public function getDescriptionHtmlAttribute(): ?string
    {
        if (! $this->description_md) {
            return null;
        }

        return \App\Helpers\MarkdownHelper::render($this->description_md);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
