<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        $urls[] = ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => route('feed'), 'changefreq' => 'daily', 'priority' => '0.8'];
        $urls[] = ['loc' => route('library.index'), 'changefreq' => 'weekly', 'priority' => '0.8'];
        $urls[] = ['loc' => route('encyclopedia.index'), 'changefreq' => 'weekly', 'priority' => '0.8'];
        $urls[] = ['loc' => route('fragments.index'), 'changefreq' => 'weekly', 'priority' => '0.7'];

        Post::orderBy('created_at', 'desc')->get(['id', 'updated_at'])->each(function ($post) use (&$urls) {
            $urls[] = [
                'loc' => route('post.show', $post),
                'lastmod' => $post->updated_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        });

        Book::published()->get(['slug', 'updated_at'])->each(function ($book) use (&$urls) {
            $urls[] = [
                'loc' => route('library.book', $book->slug),
                'lastmod' => $book->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        });

        Chapter::whereHas('book', fn ($q) => $q->published())
            ->where('is_published', true)
            ->with('book:id,slug')
            ->get(['id', 'slug', 'book_id', 'updated_at'])
            ->each(function ($chapter) use (&$urls) {
                $urls[] = [
                    'loc' => route('library.chapter', [$chapter->book->slug, $chapter->slug]),
                    'lastmod' => $chapter->updated_at->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            });

        EncyclopediaNode::where('visibility', 'public')
            ->whereNotNull('slug')
            ->get(['id', 'slug', 'parent_id', 'updated_at'])
            ->each(function ($node) use (&$urls) {
                $urls[] = [
                    'loc' => route('encyclopedia.show', $node->getFullPath()),
                    'lastmod' => $node->updated_at->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            });

        try {
            FragmentNode::where('is_published', true)
                ->whereNotNull('slug')
                ->get(['id', 'slug', 'parent_id', 'updated_at'])
                ->each(function ($node) use (&$urls) {
                    $urls[] = [
                        'loc' => route('fragments.show', $node->getFullPath()),
                        'lastmod' => $node->updated_at->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ];
                });
        } catch (\Exception $e) {
            // Table not yet migrated — skip fragments
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
