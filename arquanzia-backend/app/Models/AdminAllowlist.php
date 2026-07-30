<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAllowlist extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'admin_allowlist';

    protected $fillable = ['email', 'role', 'created_by_email'];

    public static function isAllowed(string $email): bool
    {
        $rootAdmin = config('app.root_admin_email');
        if ($email === $rootAdmin) {
            return true;
        }

        return self::where('email', $email)->exists();
    }

    public static function getRole(string $email): ?string
    {
        $rootAdmin = config('app.root_admin_email');
        if ($email === $rootAdmin) {
            return 'admin';
        }

        return self::where('email', $email)->value('role');
    }
}
