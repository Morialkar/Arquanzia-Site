<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAllowlist;
use App\Models\AuditLog;
use App\Models\MagicLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function sendMagicLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower($request->input('email'));
        $ip = $request->ip();

        $rateLimitKey = 'admin-login:' . $ip . ':' . $email;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return back()->withErrors(['email' => 'Trop de tentatives. Réessayez plus tard.']);
        }
        RateLimiter::hit($rateLimitKey, 300);

        if (!AdminAllowlist::isAllowed($email)) {
            AuditLog::log('admin.login.denied', $email, ['reason' => 'not_in_allowlist'], $ip);
            return back()->with('status', 'Si cette adresse est autorisée, un lien vous a été envoyé.');
        }

        $result = MagicLink::createForEmail($email);
        $magicUrl = route('admin.magic', ['token' => $result['token']]);

        Mail::raw("Votre lien de connexion admin Arquanzia:\n\n{$magicUrl}\n\nCe lien expire dans 15 minutes.", function ($message) use ($email) {
            $message->to($email)
                ->subject('Connexion Admin Arquanzia');
        });

        AuditLog::log('admin.magic_link.sent', $email, [], $ip);

        return back()->with('status', 'Si cette adresse est autorisée, un lien vous a été envoyé.');
    }

    public function consumeMagicLink(Request $request, string $token): RedirectResponse
    {
        $link = MagicLink::findValidByToken($token);

        if (!$link) {
            AuditLog::log('admin.magic_link.invalid', null, ['token_prefix' => substr($token, 0, 8)], $request->ip());
            return redirect()->route('admin.login')->withErrors(['token' => 'Lien invalide ou expiré.']);
        }

        if (!AdminAllowlist::isAllowed($link->email)) {
            $link->markAsUsed();
            AuditLog::log('admin.magic_link.denied', $link->email, ['reason' => 'not_in_allowlist'], $request->ip());
            return redirect()->route('admin.login')->withErrors(['token' => 'Accès refusé.']);
        }

        $link->markAsUsed();

        $request->session()->put('admin_email', $link->email);
        $request->session()->put('admin_role', AdminAllowlist::getRole($link->email));

        AuditLog::log('admin.login.success', $link->email, [], $request->ip());

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $email = $request->session()->get('admin_email');

        AuditLog::log('admin.logout', $email, [], $request->ip());

        $request->session()->forget(['admin_email', 'admin_role']);

        return redirect()->route('admin.login');
    }
}
