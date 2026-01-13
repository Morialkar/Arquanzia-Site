<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannedHandle extends Model
{
    use HasUuids;

    protected $fillable = ['handle', 'original_user_id', 'banned_by_email'];

    public function originalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }

    public static function isBanned(string $handle): bool
    {
        return self::where('handle', strtolower($handle))->exists();
    }

    public static function banHandle(string $handle, ?string $userId, string $adminEmail): self
    {
        return self::create([
            'handle' => strtolower($handle),
            'original_user_id' => $userId,
            'banned_by_email' => $adminEmail,
        ]);
    }
}
