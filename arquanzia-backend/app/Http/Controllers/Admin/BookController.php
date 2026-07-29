<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\PostMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(): View
    {
        $books = Book::with('cover')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.books.index', ['books' => $books]);
    }

    public function create(): View
    {
        return view('admin.books.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:books,slug',
            'description_md' => 'nullable|string',
            'cover' => 'nullable|image|max:5120',
            'is_published' => 'boolean',
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('title'));

        $coverMediaId = null;
        if ($request->hasFile('cover')) {
            $coverMediaId = $this->uploadMedia($request->file('cover'));
        }

        Book::create([
            'title' => $request->input('title'),
            'author' => $request->input('author') ?: 'Créations Sortilege',
            'slug' => $slug,
            'description_md' => $request->input('description_md'),
            'cover_media_id' => $coverMediaId,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.books.index')->with('success', 'Livre créé');
    }

    public function edit(Book $book): View
    {
        $book->load(['cover', 'chapters', 'files']);
        return view('admin.books.edit', ['book' => $book]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:books,slug,' . $book->id,
            'description_md' => 'nullable|string',
            'cover' => 'nullable|image|max:5120',
            'is_published' => 'boolean',
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('title'));

        if ($request->hasFile('cover')) {
            $book->cover_media_id = $this->uploadMedia($request->file('cover'));
        }

        $book->update([
            'title' => $request->input('title'),
            'author' => $request->input('author') ?: 'Créations Sortilege',
            'slug' => $slug,
            'description_md' => $request->input('description_md'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.books.edit', $book)->with('success', 'Livre mis à jour');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Livre supprimé');
    }

    protected function uploadMedia($file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('media/original', $filename);

        $media = PostMedia::create([
            'filename' => $filename,
            'mime' => $file->getMimeType(),
        ]);

        return $media->id;
    }

    public function generateExport(Request $request, Book $book): RedirectResponse
    {
        $format = $request->input('format', 'epub');
        
        if (!in_array($format, ['epub', 'pdf'])) {
            return back()->withErrors(['format' => 'Format invalide.']);
        }

        try {
            $exportService = new BookExportService();
            
            if ($format === 'epub') {
                $exportService->generateEpub($book);
            } else {
                $exportService->generatePdf($book);
            }

            return back()->with('success', strtoupper($format) . ' généré avec succès!');
        } catch (\Exception $e) {
            return back()->withErrors(['export' => $e->getMessage()]);
        }
    }
}
