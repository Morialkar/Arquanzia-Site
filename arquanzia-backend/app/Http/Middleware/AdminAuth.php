<?php

namespace App\Http\Middleware;

use App\Models\AdminAllowlist;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if already authenticated as admin
        if ($request->session()->has('admin_email')) {
            return $next($request);
        }

        // Check if logged in as user and is an allowed admin
        if ($request->session()->has('user_id')) {
            $user = User::find($request->session()->get('user_id'));
            if ($user && AdminAllowlist::isAllowed($user->email)) {
                $request->session()->put('admin_email', $user->email);
                return $next($request);
            }
        }

        return redirect()->route('admin.login');
    }
}
