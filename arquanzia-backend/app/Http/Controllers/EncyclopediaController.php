<?php

namespace App\Http\Controllers;

use App\Models\EncyclopediaNode;
use App\Services\ViewerResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EncyclopediaController extends Controller
{
    public function __construct(
        protected ViewerResolver $viewerResolver
    ) {}

    public function index(Request $request): View
    {
        $context = $this->viewerResolver->resolve($request);

        $query = EncyclopediaNode::roots()->with(['children', 'article', 'thumbnail']);

        $nodes = $query->get();

        return view('encyclopedia.index', [
            'nodes' => $nodes,
            'context' => $context,
        ]);
    }

    public function show(Request $request, string $path): View
    {
        $context = $this->viewerResolver->resolve($request);
        $segments = explode('/', $path);
        
        $node = $this->resolveNodeByPath($segments);

        if (!$node) {
            abort(404);
        }

        if ($node->isCategory()) {
            $children = $node->children()->with(['article', 'thumbnail'])->get();

            return view('encyclopedia.category', [
                'node' => $node,
                'children' => $children,
                'ancestors' => $node->ancestors(),
                'context' => $context,
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
            'context' => $context,
            'hasAccess' => true,
            'ogTitle' => $node->title . ' — Encyclopédie · Arquanzia',
            'ogDescription' => $ogDescription,
            'ogImage' => $ogImage,
        ]);
    }

    protected function resolveNodeByPath(array $segments): ?EncyclopediaNode
    {
        $parentId = null;
        $node = null;

        foreach ($segments as $slug) {
            $query = EncyclopediaNode::where('slug', $slug);
            
            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }

            $node = $query->first();

            if (!$node) {
                return null;
            }

            $parentId = $node->id;
        }

        return $node;
    }


}
