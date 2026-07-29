<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function index(): View
    {
        $latestChapter = Chapter::where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->with('book:id,slug,title,cover_media_id')
            ->latest('published_at')
            ->first();

        $encyclopediaNodes = EncyclopediaNode::where('visibility', 'public')
            ->where('type', 'article')
            ->with(['article:node_id,cover_media_id', 'thumbnail', 'parent:id,title,slug,parent_id', 'parent.parent:id,title,slug,parent_id'])
            ->latest()
            ->limit(3)
            ->get();

        $books = Book::published()->with('cover')->orderBy('created_at', 'desc')->get();

        try {
            $fragmentItems = FragmentNode::published()
                ->where('type', 'item')
                ->with(['item.media', 'thumbnail'])
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        } catch (\Exception $e) {
            $fragmentItems = collect();
        }

        return view('homepage', compact('latestChapter', 'encyclopediaNodes', 'books', 'fragmentItems'));
    }
}
