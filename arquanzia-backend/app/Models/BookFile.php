<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookFile extends Model
{
    use HasUuids;

    protected $fillable = [
        'book_id',
        'format',
        'file_media_id',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(PostMedia::class, 'file_media_id');
    }
}
