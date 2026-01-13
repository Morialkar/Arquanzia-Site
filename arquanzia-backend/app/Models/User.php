<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'handle',
        'email',
        'notification_prefs',
        'theme_pref',
    ];

    protected $casts = [
        'notification_prefs' => 'array',
    ];

    const DEFAULT_NOTIFICATION_PREFS = [
        'new_chapters' => true,
        'announcements' => true,
        'encyclopedia' => true,
    ];

    public function getNotificationPref(string $key): bool
    {
        $prefs = $this->notification_prefs ?? self::DEFAULT_NOTIFICATION_PREFS;
        return $prefs[$key] ?? true;
    }

    public function identities(): HasMany
    {
        return $this->hasMany(UserIdentity::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function accessControl(): HasOne
    {
        return $this->hasOne(AccessControl::class);
    }

    public function isReadonly(): bool
    {
        return $this->accessControl?->is_readonly ?? false;
    }

    public function isBanned(): bool
    {
        return $this->accessControl?->is_banned ?? false;
    }

    public function isVip(): bool
    {
        $control = $this->accessControl;
        return $control && $control->vip_until && $control->vip_until->isFuture();
    }

    public function isReader(): bool
    {
        $control = $this->accessControl;
        return $control && $control->reader_until && $control->reader_until->isFuture();
    }

    public function getViewerRole(): string
    {
        $isVip = $this->isVip();
        $isReader = $this->isReader();
        
        if ($isVip && $isReader) return 'vip_reader';
        if ($isVip) return 'vip';
        if ($isReader) return 'reader';
        return 'connected';
    }
}
