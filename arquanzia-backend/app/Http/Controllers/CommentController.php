<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Services\ViewerResolver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function store(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return $this->errorResponse('Connexion requise', 401, $request);
        }

        if (!$context['can_interact']) {
            return $this->errorResponse('Interactions désactivées pour votre compte', 403, $request);
        }

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $context['user']->id,
            'content' => $request->input('content'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->load('user:id,handle'),
            ], 201);
        }

        return back()->with('success', 'Commentaire ajouté');
    }

    protected function errorResponse(string $message, int $code, Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], $code);
        }

        return back()->withErrors(['error' => $message]);
    }
}
