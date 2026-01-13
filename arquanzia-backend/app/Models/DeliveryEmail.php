<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryEmail extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'email',
        'format',
        'is_active',
        'fail_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryJobs(): HasMany
    {
        return $this->hasMany(DeliveryJob::class);
    }

    public static function getActiveForUser(string $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)->where('is_active', true)->get();
    }

    public static function countForUser(string $userId): int
    {
        return self::where('user_id', $userId)->count();
    }

    public function incrementFailCount(): void
    {
        $this->fail_count++;
        if ($this->fail_count >= 5) {
            $this->is_active = false;
        }
        $this->save();
    }

    public function resetFailCount(): void
    {
        $this->fail_count = 0;
        $this->save();
    }
}
