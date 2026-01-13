<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class PageView extends Model
{
    protected $fillable = [
        'viewable_type',
        'viewable_id',
        'user_id',
        'viewed_date',
    ];

    protected $casts = [
        'viewed_date' => 'date',
    ];

    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(string $type, string $id, ?string $userId = null): void
    {
        self::create([
            'viewable_type' => $type,
            'viewable_id' => $id,
            'user_id' => $userId,
            'viewed_date' => now()->toDateString(),
        ]);
    }

    public static function getTopBooks(int $days = 30, int $limit = 10): \Illuminate\Support\Collection
    {
        return self::where('viewable_type', 'book')
            ->where('viewed_date', '>=', now()->subDays($days))
            ->select('viewable_id', DB::raw('count(*) as views'))
            ->groupBy('viewable_id')
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $book = Book::find($row->viewable_id);
                return $book ? ['book' => $book, 'views' => $row->views] : null;
            })
            ->filter();
    }

    public static function getTopChapters(int $days = 30, int $limit = 10): \Illuminate\Support\Collection
    {
        return self::where('viewable_type', 'chapter')
            ->where('viewed_date', '>=', now()->subDays($days))
            ->select('viewable_id', DB::raw('count(*) as views'))
            ->groupBy('viewable_id')
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $chapter = Chapter::with('book')->find($row->viewable_id);
                return $chapter ? ['chapter' => $chapter, 'views' => $row->views] : null;
            })
            ->filter();
    }

    public static function getTopEncyclopedia(int $days = 30, int $limit = 10): \Illuminate\Support\Collection
    {
        return self::where('viewable_type', 'encyclopedia')
            ->where('viewed_date', '>=', now()->subDays($days))
            ->select('viewable_id', DB::raw('count(*) as views'))
            ->groupBy('viewable_id')
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $node = EncyclopediaNode::find($row->viewable_id);
                return $node ? ['node' => $node, 'views' => $row->views] : null;
            })
            ->filter();
    }

    public static function getReadingResumeRate(int $days = 30): float
    {
        $usersWithProgress = ReadingProgress::where('updated_at', '>=', now()->subDays($days))
            ->where('progress', '>', 0)
            ->where('progress', '<', 100)
            ->distinct('user_id')
            ->count('user_id');

        $usersWhoResumed = ReadingProgress::where('updated_at', '>=', now()->subDays($days))
            ->where('progress', '>', 0)
            ->whereRaw('updated_at > created_at')
            ->distinct('user_id')
            ->count('user_id');

        return $usersWithProgress > 0 ? round(($usersWhoResumed / $usersWithProgress) * 100, 1) : 0;
    }
}
