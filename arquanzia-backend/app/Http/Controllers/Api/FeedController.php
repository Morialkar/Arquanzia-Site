<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $viewer = $request->query('viewer', 'public');

        if (!in_array($viewer, ['public', 'connected', 'vip', 'reader', 'vip_reader'])) {
            $viewer = 'public';
        }

        $posts = Post::with(['media', 'reactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = $posts->through(fn($post) => $post->toFeedArray($viewer));

        return response()->json($data);
    }

    public function show(Request $request, Post $post): JsonResponse
    {
        $viewer = $request->query('viewer', 'public');

        if (!in_array($viewer, ['public', 'connected', 'vip', 'reader', 'vip_reader'])) {
            $viewer = 'public';
        }

        $post->load(['media', 'reactions', 'comments' => function ($q) {
            $q->with('user:id,handle')->limit(50);
        }]);

        $data = $post->toFeedArray($viewer);

        if ($post->isAccessibleBy($viewer)) {
            $data['comments'] = $post->comments->map(fn($c) => [
                'id' => $c->id,
                'body' => $c->body,
                'user_handle' => $c->user->handle ?? 'Anonyme',
                'created_at' => $c->created_at->toIso8601String(),
            ])->toArray();
        }

        return response()->json($data);
    }
}
