<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PersonalToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = ['token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generate(string $userId, string $name): array
    {
        $plainToken = Str::random(64);

        $token = self::create([
            'user_id' => $userId,
            'name' => $name,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addYear(),
        ]);

        return [
            'token' => $token,
            'plain_token' => $plainToken,
        ];
    }

    public static function findByPlainToken(string $plainToken): ?self
    {
        return self::where('token', hash('sha256', $plainToken))
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    public function touchLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
