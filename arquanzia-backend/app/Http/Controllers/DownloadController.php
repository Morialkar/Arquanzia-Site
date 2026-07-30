<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaGalleryImage;
use App\Services\BookExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    public function __construct(
        protected BookExportService $bookExportService
    ) {}

    public function downloadBook(Request $request, string $slug, string $format): BinaryFileResponse|StreamedResponse|Response
    {
        $book = Book::published()->where('slug', $slug)->firstOrFail();

        $options = [
            'font' => $request->get('font', 'standard'),
            'size' => $request->get('size', 18),
        ];

        try {
            $result = $this->bookExportService->export($book, $format, $options);

            return response($result['content'], 200, [
                'Content-Type' => $result['mime'],
                'Content-Disposition' => 'attachment; filename="'.$result['filename'].'"',
            ]);
        } catch (\Throwable $e) {
            return $this->exportFailed($e, ['book' => $book->slug, 'format' => $format]);
        }
    }

    public function downloadChapter(Request $request, string $bookSlug, string $chapterSlug, string $format): BinaryFileResponse|Response
    {
        $book = Book::published()->where('slug', $bookSlug)->firstOrFail();
        $chapter = Chapter::where('book_id', $book->id)
            ->where('slug', $chapterSlug)
            ->where('is_published', true)
            ->firstOrFail();

        $options = [
            'font' => $request->get('font', 'standard'),
            'size' => $request->get('size', 18),
        ];

        try {
            $result = $this->bookExportService->export($chapter, $format, $options);

            return response($result['content'], 200, [
                'Content-Type' => $result['mime'],
                'Content-Disposition' => 'attachment; filename="'.$result['filename'].'"',
            ]);
        } catch (\Throwable $e) {
            return $this->exportFailed($e, [
                'book' => $book->slug,
                'chapter' => $chapter->slug,
                'format' => $format,
            ]);
        }
    }

    /**
     * Journalise l'échec côté serveur et ne renvoie qu'un message neutre : le message
     * d'exception exposait chemins, requêtes SQL et détails d'implémentation au public.
     */
    protected function exportFailed(\Throwable $e, array $context): Response
    {
        Log::error('Échec de la génération du téléchargement', $context + ['exception' => $e]);

        return response()->view('errors.500', [], 500);
    }

    public function downloadImage(string $imageId): BinaryFileResponse|Response|StreamedResponse
    {
        $image = EncyclopediaGalleryImage::find($imageId);

        if (! $image) {
            abort(404);
        }

        if (! $image->downloadable) {
            abort(403);
        }

        $media = $image->media;
        if (! $media) {
            abort(404);
        }

        $filename = $image->caption
            ? \Illuminate\Support\Str::slug($image->caption).'.jpg'
            : 'encyclopedia-image-'.$image->id.'.jpg';

        $possiblePaths = [];
        if ($media->filename) {
            $possiblePaths[] = 'media/'.$media->filename;
            $possiblePaths[] = 'media/original/'.$media->filename;
        }
        if ($media->original_path) {
            $possiblePaths[] = $media->original_path;
        }

        $foundPath = null;
        foreach ($possiblePaths as $path) {
            if (\Storage::disk('local')->exists($path)) {
                $foundPath = $path;
                break;
            }
        }

        if (! $foundPath) {
            abort(404);
        }

        return \Storage::disk('local')->download($foundPath, $filename, [
            'Content-Type' => $media->mime,
        ]);
    }
}
