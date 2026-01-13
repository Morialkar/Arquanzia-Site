<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessControl extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'is_readonly',
        'is_banned',
        'ban_reason',
        'banned_at',
        'vip_until',
        'reader_until',
    ];

    protected $casts = [
        'is_readonly' => 'boolean',
        'is_banned' => 'boolean',
        'banned_at' => 'datetime',
        'vip_until' => 'datetime',
        'reader_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
