<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FragmentItem;
use App\Models\FragmentNode;
use App\Models\PostMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FragmentController extends Controller
{
    public function index(): View
    {
        $nodes = FragmentNode::roots()->with('children')->get();

        return view('admin.fragments.index', ['nodes' => $nodes]);
    }

    public function create(Request $request): View
    {
        $parentId = $request->query('parent');
        $parent = $parentId ? FragmentNode::find($parentId) : null;
        $categories = FragmentNode::where('type', 'category')->get();

        return view('admin.fragments.create', [
            'parent' => $parent,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'type' => 'required|in:category,item',
            'parent_id' => 'nullable|uuid|exists:fragment_nodes,id',
            'description_md' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:5120',
            'order_index' => 'integer|min:0',
            'is_published' => 'nullable|boolean',
            'media_type' => 'required_if:type,item|in:image,video,pdf,coloring',
            'is_downloadable' => 'nullable|boolean',
            'video_url' => 'nullable|url|max:500',
            'media_file' => 'nullable|file|max:20480',
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('title'));

        $thumbnailMediaId = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailMediaId = $this->uploadMedia($request->file('thumbnail'));
        }

        $node = FragmentNode::create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'type' => $request->input('type'),
            'parent_id' => $request->input('parent_id'),
            'description_md' => $request->input('description_md'),
            'thumbnail_media_id' => $thumbnailMediaId,
            'order_index' => $request->input('order_index', 0),
            'is_published' => $request->boolean('is_published'),
        ]);

        if ($request->input('type') === 'item') {
            $mediaId = null;
            if ($request->hasFile('media_file')) {
                $mediaId = $this->uploadMedia($request->file('media_file'));
            }

            FragmentItem::create([
                'node_id' => $node->id,
                'media_id' => $mediaId,
                'video_url' => $request->input('video_url'),
                'media_type' => $request->input('media_type'),
                'is_downloadable' => $request->boolean('is_downloadable'),
            ]);
        }

        return redirect()->route('admin.fragments.index')->with('success', 'Fragment créé');
    }

    public function edit(FragmentNode $fragment): View
    {
        $fragment->load(['item.media', 'thumbnail']);
        $categories = FragmentNode::where('type', 'category')->where('id', '!=', $fragment->id)->get();

        return view('admin.fragments.edit', [
            'node' => $fragment,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, FragmentNode $fragment): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|uuid|exists:fragment_nodes,id',
            'description_md' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:5120',
            'order_index' => 'integer|min:0',
            'is_published' => 'nullable|boolean',
            'media_type' => 'required_if:type,item|in:image,video,pdf,coloring',
            'is_downloadable' => 'nullable|boolean',
            'video_url' => 'nullable|url|max:500',
            'media_file' => 'nullable|file|max:20480',
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('title'));

        $thumbnailMediaId = $fragment->thumbnail_media_id;
        if ($request->hasFile('thumbnail')) {
            $thumbnailMediaId = $this->uploadMedia($request->file('thumbnail'));
        }

        $fragment->update([
            'title' => $request->input('title'),
            'slug' => $slug,
            'parent_id' => $request->input('parent_id'),
            'description_md' => $request->input('description_md'),
            'thumbnail_media_id' => $thumbnailMediaId,
            'order_index' => $request->input('order_index', 0),
            'is_published' => $request->boolean('is_published'),
        ]);

        if ($fragment->isItem()) {
            $mediaId = $fragment->item?->media_id;
            if ($request->hasFile('media_file')) {
                $mediaId = $this->uploadMedia($request->file('media_file'));
            }

            FragmentItem::updateOrCreate(
                ['node_id' => $fragment->id],
                [
                    'media_id' => $mediaId,
                    'video_url' => $request->input('video_url'),
                    'media_type' => $request->input('media_type', $fragment->item?->media_type ?? 'image'),
                    'is_downloadable' => $request->boolean('is_downloadable'),
                ]
            );
        }

        return redirect()->route('admin.fragments.edit', $fragment)->with('success', 'Fragment mis à jour');
    }

    public function destroy(FragmentNode $fragment): RedirectResponse
    {
        $fragment->delete();

        return redirect()->route('admin.fragments.index')->with('success', 'Fragment supprimé');
    }

    protected function uploadMedia($file): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('media/original', $filename);

        $media = PostMedia::create([
            'filename' => $filename,
            'mime' => $file->getMimeType(),
        ]);

        return $media->id;
    }
}
