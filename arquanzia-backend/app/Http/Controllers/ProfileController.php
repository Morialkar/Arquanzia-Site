<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use App\Models\UserEntitlement;
use App\Services\ViewerResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function show(Request $request, string $handle): View
    {
        $context = $this->viewerResolver->resolve($request);

        $user = User::where('handle', $handle)->firstOrFail();

        // Get badges
        $badges = [];
        $entitlements = UserEntitlement::where('user_id', $user->id)
            ->where('ends_at', '>', now())
            ->pluck('type')
            ->toArray();

        if (in_array('reader', $entitlements)) {
            $badges[] = ['label' => 'Lecteur', 'color' => 'amber'];
        }
        if (in_array('vip', $entitlements)) {
            $badges[] = ['label' => 'VIP', 'color' => 'purple'];
        }

        // Get recent comments (public only)
        $recentComments = Comment::where('user_id', $user->id)
            ->with(['post' => function ($q) {
                $q->where('audience', 'public');
            }])
            ->whereHas('post', function ($q) {
                $q->where('audience', 'public');
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('profile.show', [
            'context' => $context,
            'profileUser' => $user,
            'badges' => $badges,
            'recentComments' => $recentComments,
        ]);
    }
}
