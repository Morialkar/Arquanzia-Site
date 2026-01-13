<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use App\Models\UserEntitlement;

class NotificationService
{
    public function notifyNewChapter(Chapter $chapter): int
    {
        $book = $chapter->book;
        $count = 0;

        // Notify all active readers
        $readerUserIds = UserEntitlement::where('type', 'reader')
            ->where('ends_at', '>', now())
            ->pluck('user_id')
            ->unique();

        foreach ($readerUserIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => Notification::TYPE_NEW_CHAPTER,
                'payload' => [
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'chapter_id' => $chapter->id,
                    'chapter_title' => $chapter->title,
                    'url' => route('library.chapter', ['book' => $book->slug, 'chapter' => $chapter->slug]),
                ],
            ]);
            $count++;
        }

        return $count;
    }

    public function notifyAnnouncement(Post $post): int
    {
        $count = 0;

        // Determine target users based on visibility
        $query = User::query();

        if ($post->visibility === 'reader') {
            $readerUserIds = UserEntitlement::where('type', 'reader')
                ->where('ends_at', '>', now())
                ->pluck('user_id');
            $query->whereIn('id', $readerUserIds);
        } elseif ($post->visibility === 'vip') {
            $vipUserIds = UserEntitlement::where('type', 'vip')
                ->where('ends_at', '>', now())
                ->pluck('user_id');
            $query->whereIn('id', $vipUserIds);
        }
        // For 'public' and 'logged_in', notify all users

        $users = $query->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => Notification::TYPE_ANNOUNCEMENT,
                'payload' => [
                    'post_id' => $post->id,
                    'title' => $post->title,
                    'excerpt' => \Str::limit(strip_tags($post->content_html), 100),
                    'url' => route('posts.show', $post->slug),
                ],
            ]);
            $count++;
        }

        return $count;
    }

    public function notifyNewEncyclopedia(EncyclopediaNode $node): int
    {
        $count = 0;

        // Only notify for public articles
        if ($node->visibility !== 'public') {
            return 0;
        }

        $users = User::all();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => Notification::TYPE_NEW_ENCYCLOPEDIA,
                'payload' => [
                    'node_id' => $node->id,
                    'title' => $node->title,
                    'excerpt' => \Str::limit($node->article?->content_md ?? '', 100),
                    'url' => route('encyclopedia.show', $node->getFullPath()),
                ],
            ]);
            $count++;
        }

        return $count;
    }

    public function notifyAccessExpiring(User $user, string $type, \DateTime $expiresAt): void
    {
        // Check if already notified recently
        $existing = Notification::where('user_id', $user->id)
            ->where('type', Notification::TYPE_ACCESS_EXPIRING)
            ->where('created_at', '>', now()->subDays(7))
            ->whereJsonContains('payload->type', $type)
            ->exists();

        if ($existing) {
            return;
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_ACCESS_EXPIRING,
            'payload' => [
                'type' => $type,
                'expires_at' => $expiresAt->format('d/m/Y'),
            ],
        ]);
    }
}
