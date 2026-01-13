<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BridgeToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'token_hash',
        'shopify_customer_id',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function createForCustomer(string $shopifyCustomerId): array
    {
        $token = Str::random(64);

        $bridgeToken = self::create([
            'token_hash' => hash('sha256', $token),
            'shopify_customer_id' => $shopifyCustomerId,
            'expires_at' => now()->addSeconds(60),
        ]);

        return ['token' => $token, 'model' => $bridgeToken];
    }

    public static function consume(string $token): ?self
    {
        $hash = hash('sha256', $token);

        $bridgeToken = self::where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($bridgeToken) {
            $bridgeToken->update(['used_at' => now()]);
        }

        return $bridgeToken;
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
