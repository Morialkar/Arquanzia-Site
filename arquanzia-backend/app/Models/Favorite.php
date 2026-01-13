<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'type', 'target_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function toggle(string $userId, string $type, string $targetId): bool
    {
        $existing = self::where('user_id', $userId)
            ->where('type', $type)
            ->where('target_id', $targetId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false; // removed
        }

        self::create([
            'user_id' => $userId,
            'type' => $type,
            'target_id' => $targetId,
        ]);
        return true; // added
    }

    public static function isFavorite(string $userId, string $type, string $targetId): bool
    {
        return self::where('user_id', $userId)
            ->where('type', $type)
            ->where('target_id', $targetId)
            ->exists();
    }

    public static function getForUser(string $userId, string $type): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)->where('type', $type)->get();
    }
}
