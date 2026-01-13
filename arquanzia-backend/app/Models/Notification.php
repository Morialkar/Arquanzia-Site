<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'payload',
        'is_read',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_read' => 'boolean',
    ];

    const TYPE_NEW_CHAPTER = 'new_chapter';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_NEW_ENCYCLOPEDIA = 'new_encyclopedia';
    const TYPE_ACCESS_EXPIRING = 'access_expiring';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getUnreadCountForUser(string $userId): int
    {
        return self::where('user_id', $userId)->where('is_read', false)->count();
    }

    public static function getForUser(string $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function markAllReadForUser(string $userId): int
    {
        return self::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function getTitle(): string
    {
        return match ($this->type) {
            self::TYPE_NEW_CHAPTER => '📖 ' . ($this->payload['chapter_title'] ?? 'Nouveau chapitre'),
            self::TYPE_ANNOUNCEMENT => '📢 ' . ($this->payload['title'] ?? 'Annonce'),
            self::TYPE_NEW_ENCYCLOPEDIA => '📜 ' . ($this->payload['title'] ?? 'Nouvel article'),
            self::TYPE_ACCESS_EXPIRING => '⏰ Votre accès expire bientôt',
            default => 'Notification',
        };
    }

    public function getDescription(): string
    {
        return match ($this->type) {
            self::TYPE_NEW_CHAPTER => ($this->payload['book_title'] ?? '') . ' — ' . ($this->payload['chapter_title'] ?? ''),
            self::TYPE_ANNOUNCEMENT => $this->payload['excerpt'] ?? '',
            self::TYPE_NEW_ENCYCLOPEDIA => $this->payload['excerpt'] ?? '',
            self::TYPE_ACCESS_EXPIRING => 'Votre accès ' . ($this->payload['type'] ?? 'Lecteur') . ' expire le ' . ($this->payload['expires_at'] ?? ''),
            default => '',
        };
    }

    public function getUrl(): ?string
    {
        return match ($this->type) {
            self::TYPE_NEW_CHAPTER => $this->payload['url'] ?? null,
            self::TYPE_ANNOUNCEMENT => $this->payload['url'] ?? null,
            self::TYPE_NEW_ENCYCLOPEDIA => $this->payload['url'] ?? null,
            default => null,
        };
    }
}
