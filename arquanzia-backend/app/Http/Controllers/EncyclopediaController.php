<?php

namespace App\Http\Controllers;

use App\Models\EncyclopediaNode;
use App\Models\Favorite;
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

        $query = EncyclopediaNode::roots()->with(['children', 'article']);

        if ($context['is_banned']) {
            $query->publicVisibility();
        }

        $nodes = $query->get();

        if ($context['is_banned']) {
            $nodes = $this->filterBannedNodes($nodes);
        }

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

        if ($context['is_banned'] && $node->isReaderOnly()) {
            abort(404);
        }

        $hasAccess = $node->isPublic() || in_array($context['viewer_tier'], ['reader', 'vip_reader']);

        if ($node->isCategory()) {
            $children = $node->children()->with('article')->get();
            
            if ($context['is_banned']) {
                $children = $children->filter(fn($n) => $n->isPublic());
            }

            return view('encyclopedia.category', [
                'node' => $node,
                'children' => $children,
                'ancestors' => $node->ancestors(),
                'context' => $context,
            ]);
        }

        $node->load(['article.cover', 'article.gallery.media']);

        $isFavorite = false;
        if ($context['user']) {
            $isFavorite = Favorite::isFavorite($context['user']->id, 'encyclopedia', $node->id);
        }

        return view('encyclopedia.article', [
            'node' => $node,
            'ancestors' => $node->ancestors(),
            'context' => $context,
            'hasAccess' => $hasAccess,
            'isFavorite' => $isFavorite,
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

    protected function filterBannedNodes($nodes)
    {
        return $nodes->filter(fn($n) => $n->isPublic())->map(function ($node) {
            if ($node->children) {
                $node->setRelation('children', $this->filterBannedNodes($node->children));
            }
            return $node;
        });
    }
}
