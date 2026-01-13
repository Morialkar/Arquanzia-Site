<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use App\Services\ViewerResolver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReactionController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function toggle(Request $request, Post $post): JsonResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return response()->json(['error' => 'Connexion requise'], 401);
        }

        if (!$context['can_interact']) {
            return response()->json(['error' => 'Interactions désactivées pour votre compte'], 403);
        }

        $request->validate([
            'type' => 'required|in:sparkle,heart,fire',
        ]);

        $type = $request->input('type');
        $userId = $context['user']->id;

        $existing = Reaction::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['action' => 'removed', 'type' => $type]);
        }

        Reaction::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'type' => $type,
        ]);

        return response()->json(['action' => 'added', 'type' => $type]);
    }
}
