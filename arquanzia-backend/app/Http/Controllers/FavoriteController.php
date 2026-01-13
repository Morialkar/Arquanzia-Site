<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\EncyclopediaNode;
use App\Models\Favorite;
use App\Services\ViewerResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $bookFavorites = Favorite::getForUser($context['user']->id, 'book');
        $encyclopediaFavorites = Favorite::getForUser($context['user']->id, 'encyclopedia');
        
        $favoriteBooks = Book::whereIn('id', $bookFavorites->pluck('target_id'))->with('cover')->get();
        $favoriteArticles = EncyclopediaNode::whereIn('id', $encyclopediaFavorites->pluck('target_id'))->with('article.cover')->get();

        return view('favorites.index', [
            'context' => $context,
            'favoriteBooks' => $favoriteBooks,
            'favoriteArticles' => $favoriteArticles,
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return redirect()->route('login');
        }

        $request->validate([
            'type' => 'required|in:book,encyclopedia',
            'target_id' => 'required|uuid',
        ]);

        $added = Favorite::toggle(
            $context['user']->id,
            $request->input('type'),
            $request->input('target_id')
        );

        $message = $added ? 'Ajouté aux favoris' : 'Retiré des favoris';

        return back()->with('success', $message);
    }
}
