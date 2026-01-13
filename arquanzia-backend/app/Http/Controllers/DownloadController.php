<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookFile;
use App\Models\Chapter;
use App\Models\ChapterFile;
use App\Services\BookExportService;
use App\Services\ViewerResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function downloadBook(Request $request, string $slug, string $format): View|BinaryFileResponse|StreamedResponse
    {
        $context = $this->viewerResolver->resolve($request);
        $hasAccess = in_array($context['viewer_tier'], ['reader', 'vip_reader']);

        if (!$hasAccess || $context['is_banned']) {
            return view('library.download-denied', [
                'context' => $context,
                'type' => 'book',
            ]);
        }

        $book = Book::where('slug', $slug)->published()->firstOrFail();
        
        $exportService = new BookExportService();
        $exportPath = $exportService->getExportPath($book, $format);
        
        if ($exportPath && Storage::disk('local')->exists($exportPath)) {
            $mimeType = $format === 'epub' ? 'application/epub+zip' : 'application/pdf';
            return Storage::disk('local')->download($exportPath, "{$book->slug}.{$format}", [
                'Content-Type' => $mimeType,
            ]);
        }
        
        $bookFile = BookFile::where('book_id', $book->id)
            ->where('format', $format)
            ->with('file')
            ->first();

        if ($bookFile && $bookFile->file) {
            $filePath = storage_path('app/media/original/' . $bookFile->file->filename);
            if (file_exists($filePath)) {
                return response()->download($filePath, "{$book->slug}.{$format}");
            }
        }

        try {
            if ($format === 'epub') {
                $exportService->generateEpub($book);
            } else {
                $exportService->generatePdf($book);
            }
            
            $exportPath = $exportService->getExportPath($book, $format);
            $mimeType = $format === 'epub' ? 'application/epub+zip' : 'application/pdf';
            return Storage::disk('local')->download($exportPath, "{$book->slug}.{$format}", [
                'Content-Type' => $mimeType,
            ]);
        } catch (\Exception $e) {
            abort(404, 'Fichier non disponible.');
        }
    }

    public function downloadChapter(Request $request, string $bookSlug, string $chapterSlug, string $format): View|BinaryFileResponse
    {
        $context = $this->viewerResolver->resolve($request);
        $hasAccess = in_array($context['viewer_tier'], ['reader', 'vip_reader']);

        if (!$hasAccess || $context['is_banned']) {
            return view('library.download-denied', [
                'context' => $context,
                'type' => 'chapter',
            ]);
        }

        $book = Book::where('slug', $bookSlug)->published()->firstOrFail();
        $chapter = Chapter::where('book_id', $book->id)
            ->where('slug', $chapterSlug)
            ->published()
            ->firstOrFail();

        $chapterFile = ChapterFile::where('chapter_id', $chapter->id)
            ->where('format', $format)
            ->with('file')
            ->firstOrFail();

        $filePath = storage_path('app/media/original/' . $chapterFile->file->filename);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, "{$book->slug}-{$chapter->slug}.{$format}");
    }
}
