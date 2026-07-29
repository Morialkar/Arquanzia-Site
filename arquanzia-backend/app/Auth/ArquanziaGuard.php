<?php

namespace App\Auth;

use Illuminate\Auth\SessionGuard;

class ArquanziaGuard extends SessionGuard
{
    /**
     * Use our existing session key instead of Laravel's default 'login_web_...' key.
     * This makes auth()->user() work seamlessly with our session('user_id') system.
     */
    public function getName(): string
    {
        return 'user_id';
    }

    /**
     * Disable the built-in remember me cookie (we use our own RememberLoginService).
     */
    public function getRecallerName(): string
    {
        return '_auth_recaller_disabled';
    }
}
