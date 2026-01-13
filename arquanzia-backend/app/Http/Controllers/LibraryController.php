<?php

namespace App\Http\Controllers;

use App\Models\AdminAllowlist;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Favorite;
use App\Models\ReadingProgress;
use App\Services\ViewerResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function index(Request $request): View
    {
        $context = $this->viewerResolver->resolve($request);

        if ($context['is_banned']) {
            abort(404);
        }

        $isAdmin = $context['user'] && AdminAllowlist::isAllowed($context['user']->email);

        $query = Book::with('cover')->orderBy('created_at', 'desc');
        
        if (!$isAdmin) {
            $query->published();
        }

        $books = $query->get();

        return view('library.index', [
            'books' => $books,
            'context' => $context,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function showBook(Request $request, string $slug): View
    {
        $context = $this->viewerResolver->resolve($request);
        $book = Book::where('slug', $slug)->with(['cover', 'chapters', 'files'])->firstOrFail();

        $isAdmin = $context['user'] && AdminAllowlist::isAllowed($context['user']->email);

        if (!$book->is_published && !$isAdmin) {
            abort(404);
        }

        $hasAccess = $isAdmin || in_array($context['viewer_tier'], ['reader', 'vip_reader']);
        
        $readingProgress = null;
        $isFavorite = false;
        if ($context['user']) {
            $readingProgress = ReadingProgress::getForUser($context['user']->id, $book->id);
            $isFavorite = Favorite::isFavorite($context['user']->id, 'book', $book->id);
        }

        return view('library.book', [
            'book' => $book,
            'context' => $context,
            'hasAccess' => $hasAccess,
            'isAdmin' => $isAdmin,
            'readingProgress' => $readingProgress,
            'isFavorite' => $isFavorite,
        ]);
    }

    public function showChapter(Request $request, string $bookSlug, string $chapterSlug): View
    {
        $context = $this->viewerResolver->resolve($request);
        
        $book = Book::where('slug', $bookSlug)->firstOrFail();
        $chapter = Chapter::where('book_id', $book->id)
            ->where('slug', $chapterSlug)
            ->with('files')
            ->firstOrFail();

        $isAdmin = $context['user'] && AdminAllowlist::isAllowed($context['user']->email);

        if ((!$book->is_published || !$chapter->is_published) && !$isAdmin) {
            abort(404);
        }

        $hasAccess = $isAdmin || in_array($context['viewer_tier'], ['reader', 'vip_reader']);
        $isComingSoon = !$isAdmin && $chapter->isComingSoon();

        // Track reading progress
        if ($context['user'] && $hasAccess && !$isComingSoon) {
            ReadingProgress::updateProgress($context['user']->id, $book->id, $chapter->id);
        }

        // Get prev/next chapters
        $prevChapter = Chapter::where('book_id', $book->id)
            ->where('order_index', '<', $chapter->order_index)
            ->orderBy('order_index', 'desc')
            ->first();
            
        $nextChapter = Chapter::where('book_id', $book->id)
            ->where('order_index', '>', $chapter->order_index)
            ->orderBy('order_index', 'asc')
            ->first();
        
        // Filter unpublished for non-admins
        if (!$isAdmin) {
            if ($prevChapter && (!$prevChapter->is_published || $prevChapter->isComingSoon())) {
                $prevChapter = null;
            }
            if ($nextChapter && (!$nextChapter->is_published || $nextChapter->isComingSoon())) {
                $nextChapter = null;
            }
        }

        return view('library.chapter', [
            'book' => $book,
            'chapter' => $chapter,
            'context' => $context,
            'hasAccess' => $hasAccess,
            'isComingSoon' => $isComingSoon,
            'isAdmin' => $isAdmin,
            'prevChapter' => $prevChapter,
            'nextChapter' => $nextChapter,
        ]);
    }

    public function markChapterComplete(Request $request, string $bookSlug, string $chapterSlug)
    {
        $context = $this->viewerResolver->resolve($request);
        
        if (!$context['user']) {
            return redirect()->route('library.book', $bookSlug);
        }

        $book = Book::where('slug', $bookSlug)->firstOrFail();
        $chapter = Chapter::where('book_id', $book->id)->where('slug', $chapterSlug)->firstOrFail();

        // Mark as complete by setting progress to 100 and moving to "next" chapter (or keeping current if last)
        $nextChapter = Chapter::where('book_id', $book->id)
            ->where('order_index', '>', $chapter->order_index)
            ->orderBy('order_index', 'asc')
            ->first();

        if ($nextChapter) {
            ReadingProgress::updateProgress($context['user']->id, $book->id, $nextChapter->id, 0);
        } else {
            // Last chapter - mark with progress 100 to indicate "completed"
            ReadingProgress::updateProgress($context['user']->id, $book->id, $chapter->id, 100);
        }

        return redirect()->route('library.book', $bookSlug);
    }
}
