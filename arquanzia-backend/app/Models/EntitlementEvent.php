<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntitlementEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'source_ref',
        'months_added',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function hasBeenProcessed(string $sourceRef): bool
    {
        return self::where('source_ref', $sourceRef)->exists();
    }
}
