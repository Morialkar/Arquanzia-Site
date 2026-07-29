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

        $pinnedPost = Post::with('media')
            ->where('is_pinned', true)
            ->where('pinned_section', 'feed')
            ->first();

        $query = Post::with('media')->orderBy('created_at', 'desc');

        if ($pinnedPost) {
            $query->where('id', '!=', $pinnedPost->id);
        }

        $posts = $query->paginate(10);

        return view('feed.index', [
            'posts' => $posts,
            'pinnedPost' => $pinnedPost,
            'context' => $context,
        ]);
    }

    public function show(Request $request, Post $post): View
    {
        $context = $this->viewerResolver->resolve($request);

        $post->load('media');

        $firstMedia = $post->media->first();
        $ogImage = $firstMedia ? route('media.show', $firstMedia->id) : null;

        return view('feed.show', [
            'post' => $post,
            'context' => $context,
            'ogTitle' => $post->title . ' — Arquanzia',
            'ogDescription' => $post->preview_text ? \Illuminate\Support\Str::limit(strip_tags($post->preview_text), 160) : null,
            'ogImage' => $ogImage,
        ]);
    }
}
