<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(): View
    {
        $books = Book::published()->with('cover')->orderBy('created_at', 'desc')->get();

        return view('library.index', ['books' => $books]);
    }

    public function showBook(string $slug): View|RedirectResponse
    {
        $book = Book::published()->where('slug', $slug)->with(['cover', 'chapters', 'files'])->firstOrFail();

        $publishedChapters = $book->chapters()->where('is_published', true)->get();

        if ($publishedChapters->count() === 1) {
            $solo = $publishedChapters->first();
            if (! $solo->isComingSoon()) {
                return redirect()->route('library.chapter', [$book->slug, $solo->slug]);
            }
        }

        $ogImage = $book->cover ? route('media.show', $book->cover->id) : null;
        $ogDescription = $book->description_md
            ? \Illuminate\Support\Str::limit(strip_tags(\App\Helpers\MarkdownHelper::render($book->description_md)), 160)
            : null;

        return view('library.book', [
            'book' => $book,
            'ogTitle' => $book->title.' — Bibliothèque · Arquanzia',
            'ogDescription' => $ogDescription,
            'ogImage' => $ogImage,
        ]);
    }

    public function showChapter(string $bookSlug, string $chapterSlug): View
    {
        $book = Book::published()->where('slug', $bookSlug)->firstOrFail();

        $chapter = Chapter::where('book_id', $book->id)
            ->where('slug', $chapterSlug)
            ->where('is_published', true)
            ->with('files')
            ->firstOrFail();

        if ($chapter->isComingSoon()) {
            abort(404);
        }

        $prevChapter = Chapter::where('book_id', $book->id)
            ->where('order_index', '<', $chapter->order_index)
            ->where('is_published', true)
            ->orderBy('order_index', 'desc')
            ->first();

        $nextChapter = Chapter::where('book_id', $book->id)
            ->where('order_index', '>', $chapter->order_index)
            ->where('is_published', true)
            ->orderBy('order_index', 'asc')
            ->first();

        if ($prevChapter?->isComingSoon()) {
            $prevChapter = null;
        }
        if ($nextChapter?->isComingSoon()) {
            $nextChapter = null;
        }

        $book->load('cover');
        $ogImage = $book->cover ? route('media.show', $book->cover->id) : null;

        return view('library.chapter', [
            'book' => $book,
            'chapter' => $chapter,
            'prevChapter' => $prevChapter,
            'nextChapter' => $nextChapter,
            'ogTitle' => $chapter->title.' — '.$book->title.' · Arquanzia',
            'ogDescription' => 'Chapitre de '.$book->title.' dans la Bibliothèque d\'Arquanzia.',
            'ogImage' => $ogImage,
        ]);
    }
}
