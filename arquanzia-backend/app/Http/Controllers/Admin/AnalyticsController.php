<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\PageView;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $stats = [
            'published_books' => Book::where('is_published', true)->count(),
            'published_chapters' => Chapter::where('is_published', true)->count(),
            'published_encyclopedia' => EncyclopediaNode::published()->articles()->count(),
            'views_last_30_days' => PageView::where('viewed_date', '>=', now()->subDays(30))->count(),
        ];

        $topBooks = PageView::getTopBooks(30, 10);
        $topChapters = PageView::getTopChapters(30, 10);
        $topEncyclopedia = PageView::getTopEncyclopedia(30, 10);

        return view('admin.analytics.index', [
            'stats' => $stats,
            'topBooks' => $topBooks,
            'topChapters' => $topChapters,
            'topEncyclopedia' => $topEncyclopedia,
        ]);
    }
}
