<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\ViewerResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedPageController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function index(Request $request): View
    {
        $context = $this->viewerResolver->resolve($request);
        $viewer = $request->query('viewer') ?? $context['viewer_tier'];

        if (!in_array($viewer, ['public', 'connected', 'vip', 'reader', 'vip_reader'])) {
            $viewer = $context['viewer_tier'];
        }

        // Get pinned post for feed section
        $pinnedPost = Post::with(['media', 'reactions'])
            ->where('is_pinned', true)
            ->where('pinned_section', 'feed')
            ->first();

        $query = Post::with(['media', 'reactions'])
            ->orderBy('created_at', 'desc');

        if ($context['is_banned']) {
            $query->where('audience', 'public');
        }

        // Exclude pinned post from regular list
        if ($pinnedPost) {
            $query->where('id', '!=', $pinnedPost->id);
        }

        $posts = $query->paginate(10);

        return view('feed.index', [
            'posts' => $posts,
            'pinnedPost' => $pinnedPost,
            'viewer' => $viewer,
            'context' => $context,
        ]);
    }

    public function show(Request $request, Post $post): View
    {
        $context = $this->viewerResolver->resolve($request);
        $viewer = $request->query('viewer') ?? $context['viewer_tier'];

        if (!in_array($viewer, ['public', 'connected', 'vip', 'reader', 'vip_reader'])) {
            $viewer = $context['viewer_tier'];
        }

        if ($context['is_banned'] && $post->audience !== 'public') {
            abort(403, 'Accès refusé');
        }

        $post->load(['media', 'reactions', 'comments' => function ($q) {
            $q->with('user:id,handle')->limit(50);
        }]);

        return view('feed.show', [
            'post' => $post,
            'viewer' => $viewer,
        ]);
    }
}
