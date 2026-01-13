<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasUuids;

    protected $fillable = [
        'book_id',
        'slug',
        'title',
        'order_index',
        'content_md',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
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
        if (!$this->content_md) {
            return null;
        }
        return \App\Helpers\MarkdownHelper::render($this->content_md);
    }

    public function isComingSoon(): bool
    {
        return !$this->is_published || ($this->published_at && $this->published_at->isFuture());
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeVisible($query)
    {
        return $query->where('is_published', true);
    }
}
