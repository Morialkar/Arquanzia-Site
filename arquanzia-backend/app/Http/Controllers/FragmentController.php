<?php

namespace App\Http\Controllers;

use App\Models\FragmentNode;
use Illuminate\View\View;

class FragmentController extends Controller
{
    public function index(): View
    {
        try {
            $nodes = FragmentNode::roots()->published()->with(['children', 'thumbnail'])->get();
        } catch (\Exception $e) {
            $nodes = collect();
        }

        return view('fragments.index', ['nodes' => $nodes]);
    }

    public function show(string $path): View
    {
        try {
            $segments = explode('/', $path);
            $node = $this->resolveNodeByPath($segments);
        } catch (\Exception $e) {
            abort(404);
        }

        if (! $node || ! $node->is_published) {
            abort(404);
        }

        if ($node->isCategory()) {
            $children = $node->children()->published()->with(['item.media', 'thumbnail'])->get();

            return view('fragments.category', [
                'node' => $node,
                'children' => $children,
                'ancestors' => $node->ancestors(),
            ]);
        }

        $node->load(['item.media', 'thumbnail']);

        return view('fragments.item', [
            'node' => $node,
            'item' => $node->item,
            'ancestors' => $node->ancestors(),
        ]);
    }

    protected function resolveNodeByPath(array $segments): ?FragmentNode
    {
        $parentId = null;
        $node = null;

        foreach ($segments as $slug) {
            $query = FragmentNode::where('slug', $slug);

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
