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
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

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

    public function getDescriptionHtmlAttribute(): ?string
    {
        if (!$this->description_md) {
            return null;
        }
        return \App\Helpers\MarkdownHelper::render($this->description_md);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
