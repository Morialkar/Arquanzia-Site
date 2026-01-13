<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'post_media';

    protected $fillable = [
        'post_id',
        'position',
        'original_path',
        'locked_path',
        'mime',
        'original_width',
        'original_height',
        'locked_width',
        'locked_height',
        'filename',
        'mime_type',
        'size',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
