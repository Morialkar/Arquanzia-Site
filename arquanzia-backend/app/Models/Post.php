<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'audience',
        'title',
        'preview_text',
        'content_full',
        'is_pinned',
        'pinned_section',
        'is_announcement',
    ];

    protected $casts = [
        'audience' => 'string',
        'is_pinned' => 'boolean',
        'is_announcement' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function isAccessibleBy(string $viewerRole): bool
    {
        return match ($this->audience) {
            'public' => true,
            'connected' => in_array($viewerRole, ['connected', 'vip', 'reader', 'vip_reader']),
            'vip' => in_array($viewerRole, ['vip', 'vip_reader']),
            'reader' => in_array($viewerRole, ['reader', 'vip_reader']),
            default => false,
        };
    }

    public function toFeedArray(string $viewerRole): array
    {
        $unlocked = $this->isAccessibleBy($viewerRole);

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'preview_text' => $this->preview_text,
            'audience' => $this->audience,
            'created_at' => $this->created_at->toIso8601String(),
            'unlocked' => $unlocked,
            'comments_count' => $this->comments()->count(),
            'reactions' => $this->getReactionsSummary(),
        ];

        if ($unlocked) {
            $data['content_full'] = $this->content_full;
            $data['media'] = $this->media->map(fn($m) => [
                'id' => $m->id,
                'url' => route('media.show', ['media' => $m->id, 'unlocked' => 1]),
                'mime' => $m->mime,
                'width' => $m->original_width,
                'height' => $m->original_height,
            ])->toArray();
        } else {
            $data['media'] = $this->media->map(fn($m) => [
                'id' => $m->id,
                'url' => route('media.show', ['media' => $m->id]),
                'mime' => $m->mime,
                'width' => $m->locked_width,
                'height' => $m->locked_height,
            ])->toArray();
        }

        return $data;
    }

    protected function getReactionsSummary(): array
    {
        return $this->reactions()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
