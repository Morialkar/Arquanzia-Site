<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Ce qui a été retravaillé récemment.
 *
 * Le flux RSS annonce les parutions, par date de publication : une entrée corrigée ou étoffée
 * n'y apparaît jamais. Personne ne sait donc qu'un texte a été repris — cette page comble ce
 * trou, sans rien demander de plus à l'autrice.
 */
class RevisionsController extends Controller
{
    private const LIMITE = 30;

    public function index(): View
    {
        return view('revisions.index', [
            'revisions' => collect()
                ->concat($this->chapitres())
                ->concat($this->encyclopedie())
                ->concat($this->fragments())
                ->sortByDesc('date')
                ->values()
                ->take(self::LIMITE),
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function chapitres(): Collection
    {
        return Chapter::whereNotNull('revised_at')
            ->where('is_published', true)
            ->whereHas('book', fn ($q) => $q->published())
            ->with('book:id,slug,title')
            ->orderByDesc('revised_at')
            ->limit(self::LIMITE)
            ->get()
            ->reject(fn (Chapter $c) => $c->isComingSoon())
            ->map(fn (Chapter $c) => [
                'titre' => $c->title,
                'contexte' => $c->book->title,
                'section' => 'Bibliothèque',
                'url' => route('library.chapter', [$c->book->slug, $c->slug]),
                'date' => $c->revised_at,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function encyclopedie(): Collection
    {
        return EncyclopediaNode::whereNotNull('revised_at')
            ->published()
            ->articles()
            ->orderByDesc('revised_at')
            ->limit(self::LIMITE)
            ->get()
            ->map(fn (EncyclopediaNode $n) => [
                'titre' => $n->title,
                'contexte' => null,
                'section' => 'Encyclopédie',
                'url' => route('encyclopedia.show', $n->getFullPath()),
                'date' => $n->revised_at,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function fragments(): Collection
    {
        return FragmentNode::whereNotNull('revised_at')
            ->where('is_published', true)
            ->where('type', 'item')
            ->orderByDesc('revised_at')
            ->limit(self::LIMITE)
            ->get()
            ->map(fn (FragmentNode $f) => [
                'titre' => $f->title,
                'contexte' => null,
                'section' => 'Fragments',
                'url' => route('fragments.show', $f->getFullPath()),
                'date' => $f->revised_at,
            ]);
    }
}
