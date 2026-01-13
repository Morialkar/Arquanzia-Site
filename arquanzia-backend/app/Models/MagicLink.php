<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MagicLink extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['email', 'token_hash', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function createForEmail(string $email): array
    {
        $token = Str::random(64);

        $link = self::create([
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(15),
        ]);

        return [
            'link' => $link,
            'token' => $token,
        ];
    }

    public static function findValidByToken(string $token): ?self
    {
        $hash = hash('sha256', $token);

        return self::where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
