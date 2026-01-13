<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Post;
use App\Models\User;
use App\Services\MediaService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function index(): View
    {
        $posts = Post::with('author')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'preview_text' => 'required|string|max:500',
            'content_full' => 'nullable|string',
            'audience' => 'required|in:public,connected,vip,reader',
            'images' => 'nullable|array',
            'images.*' => 'image|max:10240',
        ]);

        $adminEmail = $request->session()->get('admin_email');
        $author = User::firstOrCreate(
            ['handle' => 'team'],
            ['handle' => 'team']
        );

        $post = Post::create([
            'author_user_id' => $author->id,
            'title' => $validated['title'],
            'preview_text' => $validated['preview_text'],
            'content_full' => $validated['content_full'] ?? '',
            'audience' => $validated['audience'],
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $this->mediaService->store($image, $post->id, $index);
            }
        }

        AuditLog::log('post.created', $adminEmail, ['post_id' => $post->id, 'title' => $post->title]);

        return redirect()->route('admin.posts.index')->with('success', 'Post créé avec succès.');
    }

    public function edit(Post $post): View
    {
        $post->load('media');
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'preview_text' => 'required|string|max:500',
            'content_full' => 'nullable|string',
            'audience' => 'required|in:public,connected,vip,reader',
            'images' => 'nullable|array',
            'images.*' => 'image|max:10240',
            'delete_media' => 'nullable|array',
        ]);

        $wasAnnouncement = $post->is_announcement;
        $isNowAnnouncement = $request->boolean('is_announcement');

        // If pinning, unpin other posts in same section
        if ($request->boolean('is_pinned')) {
            $section = $request->input('pinned_section', 'feed');
            Post::where('pinned_section', $section)
                ->where('id', '!=', $post->id)
                ->update(['is_pinned' => false, 'pinned_section' => null]);
        }

        $post->update([
            'title' => $validated['title'],
            'preview_text' => $validated['preview_text'],
            'content_full' => $validated['content_full'] ?? '',
            'audience' => $validated['audience'],
            'is_pinned' => $request->boolean('is_pinned'),
            'pinned_section' => $request->boolean('is_pinned') ? $request->input('pinned_section', 'feed') : null,
            'is_announcement' => $isNowAnnouncement,
        ]);

        // Send notifications if just became announcement
        if (!$wasAnnouncement && $isNowAnnouncement) {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyAnnouncement($post);
        }

        if (!empty($validated['delete_media'])) {
            foreach ($post->media()->whereIn('id', $validated['delete_media'])->get() as $media) {
                $this->mediaService->delete($media);
            }
        }

        if ($request->hasFile('images')) {
            $maxPosition = $post->media()->max('position') ?? -1;
            foreach ($request->file('images') as $index => $image) {
                $this->mediaService->store($image, $post->id, $maxPosition + $index + 1);
            }
        }

        AuditLog::log('post.updated', $request->session()->get('admin_email'), ['post_id' => $post->id]);

        return redirect()->route('admin.posts.index')->with('success', 'Post mis à jour.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        foreach ($post->media as $media) {
            $this->mediaService->delete($media);
        }

        AuditLog::log('post.deleted', $request->session()->get('admin_email'), ['post_id' => $post->id, 'title' => $post->title]);

        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post supprimé.');
    }
}
