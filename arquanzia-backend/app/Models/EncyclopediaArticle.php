<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EncyclopediaArticle extends Model
{
    protected $primaryKey = 'node_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'node_id',
        'content_md',
        'cover_media_id',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(EncyclopediaNode::class, 'node_id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(PostMedia::class, 'cover_media_id');
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(EncyclopediaGalleryImage::class, 'article_id', 'node_id')->orderBy('order_index');
    }

    public function getContentHtmlAttribute(): ?string
    {
        if (! $this->content_md) {
            return null;
        }

        return \App\Helpers\MarkdownHelper::render($this->content_md);
    }
}
