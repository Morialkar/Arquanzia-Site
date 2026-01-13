<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'audit_log';

    protected $fillable = ['actor_email', 'action', 'meta', 'ip'];

    protected $casts = [
        'meta' => 'array',
    ];

    public static function log(string $action, ?string $actorEmail = null, array $meta = [], ?string $ip = null): self
    {
        return self::create([
            'action' => $action,
            'actor_email' => $actorEmail,
            'meta' => $meta,
            'ip' => $ip ?? request()->ip(),
        ]);
    }
}
