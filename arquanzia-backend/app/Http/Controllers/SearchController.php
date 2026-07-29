<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Services\ViewerResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function search(Request $request): View
    {
        $context = $this->viewerResolver->resolve($request);
        $query = $request->input('q', '');
        
        $results = [
            'books' => collect(),
            'chapters' => collect(),
            'encyclopedia' => collect(),
        ];

        if (strlen($query) >= 2) {
            // Search books (title only)
            $booksQuery = Book::where('title', 'LIKE', "%{$query}%")->with('cover');
            if (!$context['is_logged_in'] || $context['is_banned']) {
                $booksQuery->where('is_published', true);
            }
            $results['books'] = $booksQuery->limit(10)->get();

            // Search chapters (title only)
            $chaptersQuery = Chapter::where('title', 'LIKE', "%{$query}%")
                ->with('book.cover');
            if (!$context['is_logged_in'] || $context['is_banned']) {
                $chaptersQuery->where('is_published', true);
            }
            $results['chapters'] = $chaptersQuery->limit(10)->get();

            // Search encyclopedia (title only)
            $results['encyclopedia'] = EncyclopediaNode::where('title', 'LIKE', "%{$query}%")
                ->published()
                ->with('article.cover')
                ->limit(10)
                ->get();
        }

        $totalResults = $results['books']->count() + $results['chapters']->count() + $results['encyclopedia']->count();

        return view('search.results', [
            'context' => $context,
            'query' => $query,
            'results' => $results,
            'totalResults' => $totalResults,
        ]);
    }

    public function api(Request $request): JsonResponse
    {
        $context = $this->viewerResolver->resolve($request);
        $query = $request->input('q', '');
        
        $results = [];

        if (strlen($query) >= 2) {
            $booksQuery = Book::where('title', 'LIKE', "%{$query}%")->with('cover');
            if (!$context['is_logged_in'] || $context['is_banned']) {
                $booksQuery->where('is_published', true);
            }
            foreach ($booksQuery->limit(5)->get() as $book) {
                $results[] = [
                    'type' => 'book',
                    'title' => $book->title,
                    'url' => route('library.book', $book->slug),
                    'thumbnail' => $book->cover ? route('media.show', ['media' => $book->cover->id, 'unlocked' => 0]) : null,
                ];
            }

            $encyclopediaQuery = EncyclopediaNode::where('title', 'LIKE', "%{$query}%")
                ->published()
                ->with('article.cover');
            foreach ($encyclopediaQuery->limit(5)->get() as $node) {
                $results[] = [
                    'type' => 'encyclopedia',
                    'title' => $node->title,
                    'url' => route('encyclopedia.show', $node->getFullPath()),
                    'thumbnail' => $node->article?->cover ? route('media.show', ['media' => $node->article->cover->id, 'unlocked' => 0]) : null,
                ];
            }
        }

        return response()->json($results);
    }
}
