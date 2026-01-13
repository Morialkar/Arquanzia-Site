<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EntitlementState extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'ends_at',
        'updated_source',
        'updated_by_admin_email',
    ];

    protected $casts = [
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    public function extendByMonths(int $months, string $source, ?string $adminEmail = null): void
    {
        $base = $this->ends_at && $this->ends_at->isFuture() 
            ? $this->ends_at 
            : Carbon::now();

        $this->update([
            'ends_at' => $base->copy()->addMonths($months),
            'updated_source' => $source,
            'updated_by_admin_email' => $adminEmail,
        ]);
    }

    public static function findOrCreateForUser(string $userId, string $type): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId, 'type' => $type],
            ['ends_at' => null, 'updated_source' => 'admin_manual']
        );
    }
}
