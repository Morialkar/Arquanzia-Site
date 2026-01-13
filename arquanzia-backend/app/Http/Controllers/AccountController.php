<?php

namespace App\Http\Controllers;

use App\Models\BannedHandle;
use App\Models\DeliveryEmail;
use App\Models\DeliveryJob;
use App\Models\PersonalToken;
use App\Services\EntitlementService;
use App\Services\HandleBanService;
use App\Services\ViewerResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver,
        protected EntitlementService $entitlementService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $tokens = PersonalToken::where('user_id', $context['user']->id)->get();

        return view('account.index', [
            'context' => $context,
            'user' => $context['user'],
            'tokens' => $tokens,
        ]);
    }

    public function access(Request $request): View|RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $entitlements = $this->entitlementService->getUserEntitlements($context['user']);
        $deliveryEmails = DeliveryEmail::where('user_id', $context['user']->id)->get();
        $deliveryHistory = DeliveryJob::getRecentForUser($context['user']->id);

        return view('account.access', [
            'context' => $context,
            'user' => $context['user'],
            'entitlements' => $entitlements,
            'deliveryEmails' => $deliveryEmails,
            'deliveryHistory' => $deliveryHistory,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $handle = $request->input('handle');
        $user = $context['user'];

        // Admins bypass profanity/impersonation checks
        $isAdmin = \App\Models\AdminAllowlist::isAllowed($user->email);
        
        if (!$isAdmin) {
            // Check if handle is banned or contains banned words
            if (BannedHandle::isBanned($handle) || HandleBanService::containsBannedWord($handle)) {
                return back()->withErrors(['handle' => 'Ce pseudo n\'est pas disponible.']);
            }
        }

        $request->validate([
            'handle' => 'required|string|max:30|regex:/^[a-zA-Z0-9_\- ]+$/|unique:users,handle,' . $context['user']->id,
        ], [
            'handle.regex' => 'Le pseudo ne peut contenir que des lettres, chiffres, tirets, underscores et espaces.',
            'handle.unique' => 'Ce pseudo est déjà pris.',
        ]);

        $context['user']->update([
            'handle' => $request->input('handle'),
        ]);

        return redirect()->route('account.index')->with('success', 'Pseudo mis à jour');
    }

    public function refresh(Request $request): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        // Recalculate entitlements (the service already does this on each call)
        $this->entitlementService->getUserEntitlements($context['user']);

        return redirect()->route('account.access')->with('refreshed', true);
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $context['user']->update([
            'notification_prefs' => [
                'new_chapters' => $request->boolean('new_chapters'),
                'announcements' => $request->boolean('announcements'),
                'encyclopedia' => $request->boolean('encyclopedia'),
            ],
        ]);

        return redirect()->route('account.index')->with('notif_success', 'Préférences enregistrées.');
    }

    public function updateTheme(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Non connecté'], 401);
            }
            return redirect()->route('login');
        }

        $themeField = $request->has('theme') ? 'theme' : 'theme_pref';
        $request->validate([
            $themeField => 'required|in:system,light,dark',
        ]);

        $context['user']->update([
            'theme_pref' => $request->input($themeField),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('account.index')->with('theme_success', 'Thème mis à jour.');
    }

    public function createToken(Request $request): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $request->validate(['name' => 'required|string|max:50']);

        $count = PersonalToken::where('user_id', $context['user']->id)->count();
        if ($count >= 3) {
            return back()->withErrors(['token' => 'Maximum 3 tokens autorisés.']);
        }

        $result = PersonalToken::generate($context['user']->id, $request->input('name'));

        return redirect()->route('account.index')->with('new_token', $result['plain_token']);
    }

    public function revokeToken(Request $request, PersonalToken $token): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in'] || $token->user_id !== $context['user']->id) {
            abort(403);
        }

        $token->delete();

        return back()->with('success', 'Token révoqué.');
    }
}
