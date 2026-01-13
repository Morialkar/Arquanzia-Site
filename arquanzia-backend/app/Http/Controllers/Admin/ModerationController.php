<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessControl;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function index(): View
    {
        $recentComments = Comment::with(['user', 'post'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $flaggedUsers = User::whereHas('accessControl', function ($q) {
            $q->where('is_readonly', true)->orWhere('is_banned', true);
        })->with('accessControl')->get();

        return view('admin.moderation.index', [
            'recentComments' => $recentComments,
            'flaggedUsers' => $flaggedUsers,
        ]);
    }

    public function deleteComment(Request $request, Comment $comment): RedirectResponse
    {
        AuditLog::log('comment.deleted', $request->session()->get('admin_email'), [
            'comment_id' => $comment->id,
            'post_id' => $comment->post_id,
            'user_id' => $comment->user_id,
        ]);

        $comment->delete();

        return back()->with('success', 'Commentaire supprimé.');
    }

    public function toggleReadonly(Request $request, User $user): RedirectResponse
    {
        $control = AccessControl::firstOrCreate(
            ['user_id' => $user->id],
            ['is_readonly' => false, 'is_banned' => false]
        );

        $control->is_readonly = !$control->is_readonly;
        $control->save();

        AuditLog::log('user.readonly.toggled', $request->session()->get('admin_email'), [
            'user_id' => $user->id,
            'is_readonly' => $control->is_readonly,
        ]);

        $status = $control->is_readonly ? 'en lecture seule' : 'autorisé à commenter';
        return back()->with('success', "Utilisateur {$status}.");
    }

    public function toggleBan(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $control = AccessControl::firstOrCreate(
            ['user_id' => $user->id],
            ['is_readonly' => false, 'is_banned' => false]
        );

        $control->is_banned = !$control->is_banned;
        if ($control->is_banned) {
            $control->ban_reason = $request->input('reason');
            $control->banned_at = now();
        } else {
            $control->ban_reason = null;
            $control->banned_at = null;
        }
        $control->save();

        AuditLog::log('user.ban.toggled', $request->session()->get('admin_email'), [
            'user_id' => $user->id,
            'is_banned' => $control->is_banned,
            'reason' => $control->ban_reason,
        ]);

        $status = $control->is_banned ? 'banni' : 'débanni';
        return back()->with('success', "Utilisateur {$status}.");
    }
}
