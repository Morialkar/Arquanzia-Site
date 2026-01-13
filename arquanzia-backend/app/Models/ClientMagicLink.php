<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClientMagicLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'email',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function createForEmail(string $email): array
    {
        $email = self::normalizeEmail($email);
        $token = Str::random(64);

        $magicLink = self::create([
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(15),
        ]);

        return ['token' => $token, 'model' => $magicLink];
    }

    public static function consume(string $token): ?self
    {
        $hash = hash('sha256', $token);

        $magicLink = self::where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($magicLink) {
            $magicLink->update(['used_at' => now()]);
        }

        return $magicLink;
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}
