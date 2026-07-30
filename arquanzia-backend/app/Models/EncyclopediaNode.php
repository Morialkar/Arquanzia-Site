<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EncyclopediaNode extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'parent_id',
        'type',
        'slug',
        'title',
        'is_published',
        'teaser_md',
        'thumbnail_media_id',
        'order_index',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(EncyclopediaNode::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(EncyclopediaNode::class, 'parent_id')->orderBy('order_index');
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(PostMedia::class, 'thumbnail_media_id');
    }

    public function article(): HasOne
    {
        return $this->hasOne(EncyclopediaArticle::class, 'node_id');
    }

    public function isCategory(): bool
    {
        return $this->type === 'category';
    }

    public function isArticle(): bool
    {
        return $this->type === 'article';
    }

    public function isDraft(): bool
    {
        return ! $this->is_published;
    }

    public function getTeaserHtmlAttribute(): ?string
    {
        if (! $this->teaser_md) {
            return null;
        }

        return \App\Helpers\MarkdownHelper::render($this->teaser_md);
    }

    public function getFullPath(): string
    {
        $path = [$this->slug];
        $node = $this;

        while ($node->parent) {
            $node = $node->parent;
            array_unshift($path, $node->slug);
        }

        return implode('/', $path);
    }

    public function ancestors(): array
    {
        $ancestors = [];
        $node = $this->parent;

        while ($node) {
            array_unshift($ancestors, $node);
            $node = $node->parent;
        }

        return $ancestors;
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('order_index');
    }

    public function scopeCategories($query)
    {
        return $query->where('type', 'category');
    }

    public function scopeArticles($query)
    {
        return $query->where('type', 'article');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
