<?php

namespace App\Http\Controllers;

use App\Models\ClientMagicLink;
use App\Models\EntitlementState;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ClientAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function sendMagicLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = ClientMagicLink::normalizeEmail($request->input('email'));
        $key = 'magic-link:' . $email;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        RateLimiter::hit($key, 300);

        $result = ClientMagicLink::createForEmail($email);
        $magicUrl = route('magic.consume', ['token' => $result['token']]);

        Mail::raw(
            "Cliquez sur ce lien pour vous connecter à Arquanzia:\n\n{$magicUrl}\n\nCe lien expire dans 15 minutes.",
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Votre lien de connexion Arquanzia');
            }
        );

        return back()->with('status', 'Un lien de connexion a été envoyé à votre adresse email.');
    }

    public function consumeMagicLink(string $token): RedirectResponse
    {
        $magicLink = ClientMagicLink::consume($token);

        if (!$magicLink) {
            return redirect()->route('login')->withErrors([
                'email' => 'Lien invalide ou expiré.',
            ]);
        }

        $user = $this->findOrCreateUserByEmail($magicLink->email);

        $this->ensureRootEntitlements($user);

        session(['user_id' => $user->id]);

        return redirect()->route('feed');
    }

    public function logout(): RedirectResponse
    {
        session()->forget('user_id');
        
        return redirect()->route('feed');
    }

    protected function findOrCreateUserByEmail(string $email): User
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            return $user;
        }

        $baseHandle = 'user_' . substr(md5($email), 0, 8);
        $handle = $baseHandle;
        $suffix = 1;

        while (User::where('handle', $handle)->exists()) {
            $handle = $baseHandle . '_' . $suffix;
            $suffix++;
        }

        return User::create([
            'email' => $email,
            'handle' => $handle,
        ]);
    }

    protected function ensureRootEntitlements(User $user): void
    {
        $rootEmail = config('app.root_admin_email');
        
        if ($user->email !== $rootEmail) {
            return;
        }

        $farFuture = now()->addYears(10);

        EntitlementState::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'vip'],
            ['ends_at' => $farFuture, 'updated_source' => 'admin_manual']
        );

        EntitlementState::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'reader'],
            ['ends_at' => $farFuture, 'updated_source' => 'admin_manual']
        );
    }
}
