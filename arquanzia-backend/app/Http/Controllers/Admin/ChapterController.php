<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function create(Book $book): View
    {
        $nextOrder = $book->chapters()->max('order_index') + 1;

        return view('admin.chapters.create', ['book' => $book, 'nextOrder' => $nextOrder]);
    }

    public function store(Request $request, Book $book): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'order_index' => 'required|integer|min:0',
            'content_md' => 'nullable|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('title'));

        $publie = $request->boolean('is_published');

        Chapter::create([
            'book_id' => $book->id,
            'title' => $request->input('title'),
            'slug' => $slug,
            'order_index' => $request->input('order_index'),
            'content_md' => $request->input('content_md'),
            'is_published' => $publie,
            'published_at' => self::dateDeParution($request->input('published_at'), $publie),
        ]);

        return redirect()->route('admin.books.edit', $book)->with('success', 'Chapitre créé');
    }

    /**
     * Applique la promesse du formulaire : « Laisser vide = immédiat ».
     *
     * Le champ était enregistré tel quel, donc vide, donc NULL. Rien ne le montrait sur la
     * fiche du chapitre, mais tout ce qui classe par date de parution s'en trouvait faussé :
     * MySQL range les NULL en dernier, si bien qu'un chapitre publié le jour même passait
     * derrière de vieux chapitres datés, sur la page d'accueil comme dans le flux Atom.
     *
     * Un brouillon garde une date vide : il ne paraît pas encore.
     */
    private static function dateDeParution(?string $saisie, bool $publie, ?\Illuminate\Support\Carbon $actuelle = null): ?\Illuminate\Support\Carbon
    {
        if ($saisie) {
            return \Illuminate\Support\Carbon::parse($saisie);
        }

        if (! $publie) {
            return null;
        }

        return $actuelle ?? now();
    }

    public function edit(Book $book, Chapter $chapter): View
    {
        return view('admin.chapters.edit', ['book' => $book, 'chapter' => $chapter]);
    }

    public function update(Request $request, Book $book, Chapter $chapter): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'order_index' => 'required|integer|min:0',
            'content_md' => 'nullable|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('title'));

        $nowPublished = $request->boolean('is_published');
        $publishedAt = self::dateDeParution(
            $request->input('published_at'),
            $nowPublished,
            $chapter->published_at,
        );

        $chapter->update([
            'title' => $request->input('title'),
            'slug' => $slug,
            'order_index' => $request->input('order_index'),
            'content_md' => $request->input('content_md'),
            'is_published' => $nowPublished,
            'published_at' => $publishedAt,
            'promo_banner_enabled' => $request->boolean('promo_banner_enabled'),
            'promo_banner_text' => $request->input('promo_banner_text'),
            'promo_banner_button_label' => $request->input('promo_banner_button_label'),
            'promo_banner_button_url' => $request->input('promo_banner_button_url'),
        ]);

        return redirect()->route('admin.books.edit', $book)->with('success', 'Chapitre mis à jour');
    }

    public function destroy(Book $book, Chapter $chapter): RedirectResponse
    {
        $chapter->delete();

        return redirect()->route('admin.books.edit', $book)->with('success', 'Chapitre supprimé');
    }
}
