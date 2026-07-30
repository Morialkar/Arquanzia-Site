<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EncyclopediaNode;
use App\Models\Wikilink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WikilinkController extends Controller
{
    public function index(): View
    {
        $wikilinks = Wikilink::with('encyclopediaNode')
            ->orderBy('term')
            ->paginate(50);

        return view('admin.wikilinks.index', ['wikilinks' => $wikilinks]);
    }

    public function create(): View
    {
        $nodes = EncyclopediaNode::articles()
            ->orderBy('title')
            ->get();

        return view('admin.wikilinks.create', ['nodes' => $nodes]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'term' => 'required|string|max:255|unique:wikilinks,term',
            'encyclopedia_node_id' => 'nullable|uuid|exists:encyclopedia_nodes,id',
            'custom_url' => 'nullable|url|max:500',
        ]);

        Wikilink::create($request->only(['term', 'encyclopedia_node_id', 'custom_url']));

        return redirect()->route('admin.wikilinks.index')
            ->with('success', 'Wikilink créé');
    }

    public function edit(Wikilink $wikilink): View
    {
        $nodes = EncyclopediaNode::articles()
            ->orderBy('title')
            ->get();

        return view('admin.wikilinks.edit', [
            'wikilink' => $wikilink,
            'nodes' => $nodes,
        ]);
    }

    public function update(Request $request, Wikilink $wikilink): RedirectResponse
    {
        $request->validate([
            'term' => 'required|string|max:255|unique:wikilinks,term,'.$wikilink->id,
            'encyclopedia_node_id' => 'nullable|uuid|exists:encyclopedia_nodes,id',
            'custom_url' => 'nullable|url|max:500',
        ]);

        $wikilink->update($request->only(['term', 'encyclopedia_node_id', 'custom_url']));

        return redirect()->route('admin.wikilinks.index')
            ->with('success', 'Wikilink mis à jour');
    }

    public function destroy(Wikilink $wikilink): RedirectResponse
    {
        $wikilink->delete();

        return redirect()->route('admin.wikilinks.index')
            ->with('success', 'Wikilink supprimé');
    }
}
