<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaGalleryImage;
use App\Models\EncyclopediaNode;
use App\Models\PostMedia;
use App\Services\EncyclopediaImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EncyclopediaController extends Controller
{
    public function index(): View
    {
        $nodes = EncyclopediaNode::roots()
            ->with('children')
            ->orderBy('order_index')
            ->get();

        return view('admin.encyclopedia.index', ['nodes' => $nodes]);
    }

    public function create(Request $request): View
    {
        $parentId = $request->query('parent');
        $parent = $parentId ? EncyclopediaNode::find($parentId) : null;
        $categories = EncyclopediaNode::categories()->get();

        return view('admin.encyclopedia.create', [
            'parent' => $parent,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'type' => 'required|in:category,article',
            'parent_id' => 'nullable|uuid|exists:encyclopedia_nodes,id',
            'is_published' => 'nullable|boolean',
            'teaser_md' => 'nullable|string',
            'order_index' => 'integer|min:0',
            'content_md' => 'nullable|string',
            'cover' => 'nullable|image|max:5120',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('title'));

        $thumbnailMediaId = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailMediaId = $this->uploadMedia($request->file('thumbnail'));
        }

        $node = EncyclopediaNode::create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'type' => $request->input('type'),
            'parent_id' => $request->input('parent_id'),
            'is_published' => $request->boolean('is_published'),
            'teaser_md' => $request->input('teaser_md'),
            'thumbnail_media_id' => $thumbnailMediaId,
            'order_index' => $request->input('order_index', 0),
        ]);

        if ($request->input('type') === 'article') {
            $coverMediaId = null;
            if ($request->hasFile('cover')) {
                $coverMediaId = $this->uploadMedia($request->file('cover'));
            }

            EncyclopediaArticle::create([
                'node_id' => $node->id,
                'content_md' => $request->input('content_md'),
                'cover_media_id' => $coverMediaId,
            ]);
        }

        return redirect()->route('admin.encyclopedia.index')->with('success', 'Élément créé');
    }

    public function edit(EncyclopediaNode $encyclopedium): View
    {
        $encyclopedium->load(['article.cover', 'article.gallery.media']);
        $categories = EncyclopediaNode::categories()->where('id', '!=', $encyclopedium->id)->get();

        return view('admin.encyclopedia.edit', [
            'node' => $encyclopedium,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, EncyclopediaNode $encyclopedium): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|uuid|exists:encyclopedia_nodes,id',
            'is_published' => 'nullable|boolean',
            'teaser_md' => 'nullable|string',
            'order_index' => 'integer|min:0',
            'content_md' => 'nullable|string',
            'cover' => 'nullable|image|max:5120',
            'thumbnail' => 'nullable|image|max:5120',
            'gallery.*' => 'nullable|image|max:5120',
            'gallery_caption.*' => 'nullable|string|max:255',
            'gallery_downloadable.*' => 'nullable|boolean',
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('title'));

        $thumbnailMediaId = $encyclopedium->thumbnail_media_id;
        if ($request->hasFile('thumbnail')) {
            $thumbnailMediaId = $this->uploadMedia($request->file('thumbnail'));
        }

        $encyclopedium->update([
            'title' => $request->input('title'),
            'slug' => $slug,
            'parent_id' => $request->input('parent_id'),
            'is_published' => $request->boolean('is_published'),
            'teaser_md' => $request->input('teaser_md'),
            'thumbnail_media_id' => $thumbnailMediaId,
            'order_index' => $request->input('order_index', 0),
        ]);

        if ($encyclopedium->isArticle()) {
            $coverMediaId = $encyclopedium->article?->cover_media_id;
            if ($request->hasFile('cover')) {
                $coverMediaId = $this->uploadMedia($request->file('cover'));
            }

            $encyclopedium->article()->updateOrCreate(
                ['node_id' => $encyclopedium->id],
                [
                    'content_md' => $request->input('content_md'),
                    'cover_media_id' => $coverMediaId,
                ]
            );

            // Handle gallery images
            if ($request->hasFile('gallery')) {
                $maxOrder = $encyclopedium->article->gallery()->max('order_index') ?? -1;
                foreach ($request->file('gallery') as $index => $file) {
                    $mediaId = $this->uploadMedia($file);
                    EncyclopediaGalleryImage::create([
                        'article_id' => $encyclopedium->id,
                        'media_id' => $mediaId,
                        'order_index' => $maxOrder + $index + 1,
                    ]);
                }
            }

            // Update existing gallery images captions and downloadable options
            $captions = $request->input('gallery_caption', []);
            $downloadable = $request->input('gallery_downloadable', []);
            
            foreach ($encyclopedium->article->gallery as $image) {
                $image->update([
                    'caption' => $captions[$image->id] ?? null,
                    'downloadable' => isset($downloadable[$image->id]),
                ]);
            }
        }

        return redirect()->route('admin.encyclopedia.edit', ['encyclopedium' => $encyclopedium])->with('success', 'Élément mis à jour');
    }

    public function destroy(EncyclopediaNode $encyclopedium): RedirectResponse
    {
        $encyclopedium->delete();
        return redirect()->route('admin.encyclopedia.index')->with('success', 'Élément supprimé');
    }

    public function deleteGalleryImage(EncyclopediaNode $encyclopedium, EncyclopediaGalleryImage $image): RedirectResponse
    {
        $image->delete();
        return back()->with('success', 'Image supprimée');
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

    public function importForm(): View
    {
        return view('admin.encyclopedia.import');
    }

    public function importAnalyze(Request $request): View
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:51200',
        ]);

        $zipPath = $request->file('zip_file')->store('temp');
        $fullPath = storage_path('app/' . $zipPath);

        $service = new EncyclopediaImportService();
        $analysis = $service->analyzeZip($fullPath);

        session(['import_zip_path' => $fullPath]);

        return view('admin.encyclopedia.import-confirm', [
            'analysis' => $analysis,
            'zipPath' => $zipPath,
        ]);
    }

    public function importExecute(Request $request): RedirectResponse
    {
        $zipPath = session('import_zip_path');
        
        if (!$zipPath || !file_exists($zipPath)) {
            return redirect()->route('admin.encyclopedia.import')
                ->withErrors(['zip_file' => 'Session expirée, veuillez réuploader le fichier.']);
        }

        $conflictMode = $request->input('conflict_mode', 'overwrite');
        $skipPaths = $request->input('skip', []);

        $service = new EncyclopediaImportService();
        
        try {
            $result = $service->import($zipPath, $conflictMode, $skipPaths);
            
            @unlink($zipPath);
            session()->forget('import_zip_path');

            $createdCount = count($result['created']);
            $updatedCount = count(array_filter($result['imported'], fn($i) => ($i['action'] ?? '') === 'updated'));
            $skippedCount = count($result['conflicts']);

            return redirect()->route('admin.encyclopedia.index')
                ->with('success', "Import terminé: {$createdCount} créé(s), {$updatedCount} mis à jour, {$skippedCount} ignoré(s).");
        } catch (\Exception $e) {
            return redirect()->route('admin.encyclopedia.import')
                ->withErrors(['import' => $e->getMessage()]);
        }
    }
}
