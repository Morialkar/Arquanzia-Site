<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class FeedPageController extends Controller
{
    public function index(): View
    {
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
        ]);
    }

    public function show(Post $post): View
    {
        $post->load('media');

        $firstMedia = $post->media->first();
        $ogImage = $firstMedia ? route('media.show', $firstMedia->id) : null;

        return view('feed.show', [
            'post' => $post,
            'ogTitle' => $post->title.' — Arquanzia',
            'ogDescription' => $post->preview_text ? \Illuminate\Support\Str::limit(strip_tags($post->preview_text), 160) : null,
            'ogImage' => $ogImage,
        ]);
    }
}
