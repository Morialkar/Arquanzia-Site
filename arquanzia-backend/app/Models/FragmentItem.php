<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FragmentItem extends Model
{
    use HasUuids;

    protected $primaryKey = 'node_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'node_id',
        'media_id',
        'video_url',
        'media_type',
        'is_downloadable',
    ];

    protected $casts = [
        'is_downloadable' => 'boolean',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(FragmentNode::class, 'node_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(PostMedia::class, 'media_id');
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        // YouTube: watch?v= or youtu.be/
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]+)/', $this->video_url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // Vimeo: vimeo.com/{id}
        if (preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        return $this->video_url;
    }
}
