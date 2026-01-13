<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\ViewerResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $notifications = Notification::getForUser($context['user']->id);

        // Mark all as read when viewing
        Notification::markAllReadForUser($context['user']->id);

        return view('notifications.index', [
            'context' => $context,
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in'] || $notification->user_id !== $context['user']->id) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        if ($url = $notification->getUrl()) {
            return redirect($url);
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        Notification::markAllReadForUser($context['user']->id);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
