<?php

namespace App\Http\Controllers;

use App\Models\EncyclopediaNode;
use Illuminate\View\View;

class EncyclopediaController extends Controller
{
    public function index(): View
    {
        $nodes = EncyclopediaNode::roots()
            ->published()
            ->with(['children' => fn ($query) => $query->published(), 'article', 'thumbnail'])
            ->get();

        return view('encyclopedia.index', [
            'nodes' => $nodes,
        ]);
    }

    public function show(string $path): View
    {
        $segments = explode('/', $path);

        $node = $this->resolveNodeByPath($segments);

        if (! $node) {
            abort(404);
        }

        if ($node->isCategory()) {
            $children = $node->children()->published()->with(['article', 'thumbnail'])->get();

            return view('encyclopedia.category', [
                'node' => $node,
                'children' => $children,
                'ancestors' => $node->ancestors(),
            ]);
        }

        $node->load(['article.cover', 'article.gallery.media', 'thumbnail']);

        $ogImage = $node->thumbnail ? route('media.show', $node->thumbnail->id)
            : ($node->article?->cover ? route('media.show', $node->article->cover->id) : null);

        $ogDescription = $node->teaser_md
            ? \Illuminate\Support\Str::limit(strip_tags($node->teaser_html), 160)
            : 'Entrée de l\'Encyclopédie d\'Arquanzia.';

        return view('encyclopedia.article', [
            'node' => $node,
            'ancestors' => $node->ancestors(),
            'ogTitle' => $node->title.' — Encyclopédie · Arquanzia',
            'ogDescription' => $ogDescription,
            'ogImage' => $ogImage,
        ]);
    }

    protected function resolveNodeByPath(array $segments): ?EncyclopediaNode
    {
        $parentId = null;
        $node = null;

        foreach ($segments as $slug) {
            // Un nœud en brouillon rend tout son sous-arbre inaccessible, y compris par URL directe.
            $query = EncyclopediaNode::where('slug', $slug)->published();

            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }

            $node = $query->first();

            if (! $node) {
                return null;
            }

            $parentId = $node->id;
        }

        return $node;
    }
}
