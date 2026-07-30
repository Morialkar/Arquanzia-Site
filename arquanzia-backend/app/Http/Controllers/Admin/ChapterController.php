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

        Chapter::create([
            'book_id' => $book->id,
            'title' => $request->input('title'),
            'slug' => $slug,
            'order_index' => $request->input('order_index'),
            'content_md' => $request->input('content_md'),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->input('published_at'),
        ]);

        return redirect()->route('admin.books.edit', $book)->with('success', 'Chapitre créé');
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
        $publishedAt = $request->input('published_at');

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
