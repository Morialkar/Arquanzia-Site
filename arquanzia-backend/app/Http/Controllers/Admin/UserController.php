<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAllowlist;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('handle', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user,
            'adminRole' => $user->email ? AdminAllowlist::getRole($user->email) : null,
            'posts' => $user->posts()->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function create(): View
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
        $handle = $request->input('handle') ?: 'user_'.substr(md5($email), 0, 8);

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

        return redirect()->route('admin.users.show', $user)->with('success', 'Compte créé');
    }
}
