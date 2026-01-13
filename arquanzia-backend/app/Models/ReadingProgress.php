<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{
    use HasUuids;

    protected $table = 'reading_progress';

    protected $fillable = [
        'user_id',
        'book_id',
        'chapter_id',
        'progress',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public static function updateProgress(string $userId, string $bookId, string $chapterId, int $progress = 0): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'book_id' => $bookId],
            ['chapter_id' => $chapterId, 'progress' => $progress]
        );
    }

    public static function getForUser(string $userId, string $bookId): ?self
    {
        return self::where('user_id', $userId)->where('book_id', $bookId)->with('chapter')->first();
    }
}
