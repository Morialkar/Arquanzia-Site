<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class User extends Model implements Authenticatable
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'handle',
        'email',
        'notification_prefs',
        'theme_pref',
        'reader_font',
        'reader_font_size',
    ];

    protected $hidden = ['password'];

    public function getAuthIdentifierName(): string { return 'id'; }
    public function getAuthIdentifier(): mixed { return $this->id; }
    public function getAuthPassword(): string { return $this->password ?? ''; }
    public function getAuthPasswordName(): string { return 'password'; }
    public function getRememberToken(): ?string { return null; }
    public function setRememberToken($value): void {}
    public function getRememberTokenName(): string { return ''; }

    protected $casts = [
        'notification_prefs' => 'array',
        'reader_font_size' => 'integer',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_user_id');
    }
}
