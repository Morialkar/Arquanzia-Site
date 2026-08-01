<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $search,
    ) {}

    public function search(Request $request): View
    {
        $query = (string) $request->input('q', '');
        $results = $this->search->search($query);

        return view('search.results', [
            'query' => $query,
            'results' => $results,
            'totalResults' => $results->count(),
        ]);
    }

    /** Appelée à la frappe depuis l'en-tête : réponse courte, sans extrait. */
    public function api(Request $request): JsonResponse
    {
        $results = $this->search
            ->search((string) $request->input('q', ''), limit: 8)
            ->map(fn (array $r) => [
                'type' => $r['type'],
                'title' => $r['title'],
                'url' => $r['url'],
                'thumbnail' => $r['thumbnail'],
            ])
            ->values();

        return response()->json($results);
    }
}
