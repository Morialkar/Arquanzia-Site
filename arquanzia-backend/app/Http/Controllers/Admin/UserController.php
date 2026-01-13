<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\EntitlementService;
use App\Services\HandleBanService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected EntitlementService $entitlementService,
        protected HandleBanService $handleBanService
    ) {}

    public function index(Request $request): View
    {
        $query = User::with(['accessControl']);
        
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('handle', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        foreach ($users as $user) {
            $user->entitlements = $this->entitlementService->getUserEntitlements($user);
        }

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function show(User $user): View
    {
        $user->load('accessControl');
        $entitlements = $this->entitlementService->getUserEntitlements($user);

        return view('admin.users.show', [
            'user' => $user,
            'entitlements' => $entitlements,
        ]);
    }

    public function updateEntitlement(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:vip,reader',
            'ends_at' => 'nullable|date',
        ]);

        $type = $request->input('type');
        $endsAt = $request->input('ends_at') ? Carbon::parse($request->input('ends_at')) : null;
        $adminEmail = session('admin_email');

        $this->entitlementService->setEntitlementEndDate($user, $type, $endsAt, $adminEmail);

        AuditLog::create([
            'admin_email' => $adminEmail,
            'action' => 'update_entitlement',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => json_encode([
                'type' => $type,
                'ends_at' => $endsAt?->toIso8601String(),
            ]),
        ]);

        return back()->with('success', "Accès {$type} mis à jour");
    }

    public function syncOrder(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        return back()->with('status', 'Synchronisation manuelle à implémenter via API Shopify');
    }

    public function banHandle(User $user): RedirectResponse
    {
        $adminEmail = session('admin_email');
        $result = $this->handleBanService->banHandle($user, $adminEmail);

        AuditLog::create([
            'admin_email' => $adminEmail,
            'action' => 'ban_handle',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => json_encode($result),
        ]);

        $message = "Pseudo \"{$result['old_handle']}\" banni. Nouveau pseudo: {$result['new_handle']}. ({$result['ban_count']}/5)";
        
        if ($result['fully_banned']) {
            $message .= " - UTILISATEUR BANNI COMPLÈTEMENT";
        }

        return back()->with('success', $message);
    }

    public function resetHandleBans(User $user): RedirectResponse
    {
        $oldCount = $user->handle_ban_count;
        $user->handle_ban_count = 0;
        $user->save();

        AuditLog::create([
            'admin_email' => session('admin_email'),
            'action' => 'reset_handle_bans',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => json_encode(['old_count' => $oldCount]),
        ]);

        return back()->with('success', "Compteur de bans pseudo remis à 0 (était {$oldCount}/5)");
    }

    public function create(): \Illuminate\View\View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'handle' => 'nullable|string|max:30',
        ]);

        $email = $request->input('email');
        $handle = $request->input('handle') ?: 'user_' . substr(md5($email), 0, 8);

        $user = User::create([
            'email' => $email,
            'handle' => $handle,
        ]);

        AuditLog::create([
            'admin_email' => session('admin_email'),
            'action' => 'create_user',
            'target_type' => 'user',
            'target_id' => $user->id,
            'details' => json_encode(['email' => $email, 'handle' => $handle]),
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'Compte cree');
    }
}
