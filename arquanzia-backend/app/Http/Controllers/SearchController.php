<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function search(Request $request): View
    {
        $query = $request->input('q', '');

        $results = [
            'books' => collect(),
            'chapters' => collect(),
            'encyclopedia' => collect(),
        ];

        if (strlen($query) >= 2) {
            // Search books (title only)
            $results['books'] = Book::where('title', 'LIKE', "%{$query}%")
                ->where('is_published', true)
                ->with('cover')
                ->limit(10)
                ->get();

            // Search chapters (title only)
            $results['chapters'] = Chapter::where('title', 'LIKE', "%{$query}%")
                ->where('is_published', true)
                ->with('book.cover')
                ->limit(10)
                ->get();

            // Search encyclopedia (title only)
            $results['encyclopedia'] = EncyclopediaNode::where('title', 'LIKE', "%{$query}%")
                ->published()
                ->with('article.cover')
                ->limit(10)
                ->get();
        }

        $totalResults = $results['books']->count() + $results['chapters']->count() + $results['encyclopedia']->count();

        return view('search.results', [
            'query' => $query,
            'results' => $results,
            'totalResults' => $totalResults,
        ]);
    }

    public function api(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $results = [];

        if (strlen($query) >= 2) {
            $books = Book::where('title', 'LIKE', "%{$query}%")
                ->where('is_published', true)
                ->with('cover')
                ->limit(5)
                ->get();

            foreach ($books as $book) {
                $results[] = [
                    'type' => 'book',
                    'title' => $book->title,
                    'url' => route('library.book', $book->slug),
                    'thumbnail' => $book->cover ? route('media.show', $book->cover->id) : null,
                ];
            }

            $nodes = EncyclopediaNode::where('title', 'LIKE', "%{$query}%")
                ->published()
                ->with('article.cover')
                ->limit(5)
                ->get();

            foreach ($nodes as $node) {
                $results[] = [
                    'type' => 'encyclopedia',
                    'title' => $node->title,
                    'url' => route('encyclopedia.show', $node->getFullPath()),
                    'thumbnail' => $node->article?->cover ? route('media.show', $node->article->cover->id) : null,
                ];
            }
        }

        return response()->json($results);
    }
}
