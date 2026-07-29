<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\User;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'resume_rate' => PageView::getReadingResumeRate(30),
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
