<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAllowlist;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $currentEmail = $request->session()->get('admin_email');
        $rootAdmin = config('app.root_admin_email');

        $admins = AdminAllowlist::orderBy('created_at', 'desc')->get();

        return view('admin.admins.index', [
            'admins' => $admins,
            'rootAdmin' => $rootAdmin,
            'currentEmail' => $currentEmail,
            'isRoot' => $currentEmail === $rootAdmin,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentEmail = $request->session()->get('admin_email');
        $rootAdmin = config('app.root_admin_email');

        if ($currentEmail !== $rootAdmin) {
            return back()->withErrors(['permission' => 'Seul le root admin peut ajouter des admins.']);
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:admin_allowlist,email',
            'role' => 'required|in:admin,editor',
        ]);

        AdminAllowlist::create([
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'created_by_email' => $currentEmail,
        ]);

        AuditLog::log('admin.allowlist.added', $currentEmail, [
            'added_email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Admin ajouté.');
    }

    public function destroy(Request $request, AdminAllowlist $admin): RedirectResponse
    {
        $currentEmail = $request->session()->get('admin_email');
        $rootAdmin = config('app.root_admin_email');

        if ($currentEmail !== $rootAdmin) {
            return back()->withErrors(['permission' => 'Seul le root admin peut retirer des admins.']);
        }

        if ($admin->email === $rootAdmin) {
            return back()->withErrors(['permission' => 'Impossible de supprimer le root admin.']);
        }

        AuditLog::log('admin.allowlist.removed', $currentEmail, [
            'removed_email' => $admin->email,
        ]);

        $admin->delete();

        return back()->with('success', 'Admin retiré.');
    }
}
