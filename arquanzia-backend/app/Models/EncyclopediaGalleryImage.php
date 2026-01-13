<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncyclopediaGalleryImage extends Model
{
    use HasUuids;

    protected $table = 'encyclopedia_gallery';

    protected $fillable = ['article_id', 'media_id', 'order_index', 'caption'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(EncyclopediaArticle::class, 'article_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(PostMedia::class, 'media_id');
    }
}
