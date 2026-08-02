<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function index(): View
    {
        // Le fil est la seule surface qui bouge entre deux chapitres : sans lui, la porte
        // d'entrée donnait l'impression d'un site arrêté.
        $latestPost = Post::with('media')->latest()->first();

        // Le livre doit être publié lui aussi : sinon la page d'accueil menait vers un chapitre
        // dont la fiche répond 404.
        $latestChapter = Chapter::where('is_published', true)
            ->whereHas('book', fn ($q) => $q->published())
            ->with('book:id,slug,title,cover_media_id')
            ->get()
            ->reject(fn (Chapter $chapitre) => $chapitre->isComingSoon())
            ->sortByDesc(fn (Chapter $chapitre) => $chapitre->lastTouchedAt())
            ->first();

        $encyclopediaNodes = EncyclopediaNode::published()
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

        return view('homepage', compact('latestPost', 'latestChapter', 'encyclopediaNodes', 'books', 'fragmentItems'));
    }
}
