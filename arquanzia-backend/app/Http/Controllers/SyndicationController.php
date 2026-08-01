<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\FeedBuilder;
use App\Support\FeedSelection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SyndicationController extends Controller
{
    public function __construct(
        protected FeedBuilder $builder,
    ) {}

    /** Page de composition : cases à cocher, URL construite en direct, aucun état serveur. */
    public function builder(): View
    {
        return view('feeds.builder', [
            'books' => Book::published()->orderBy('title')->get(['slug', 'title']),
            'sections' => FeedSelection::SECTIONS,
        ]);
    }

    public function atom(Request $request): Response|RedirectResponse
    {
        $selection = FeedSelection::fromRequest($request);

        if ($selection->exceedsBookLimit()) {
            abort(400, 'Un flux ne peut pas suivre plus de '.FeedSelection::MAX_BOOKS.' livres.');
        }

        // Un slug inconnu est refusé plutôt qu'ignoré : un flux qui se vide en silence est
        // pire qu'une erreur visible, car personne ne vient signaler un flux muet.
        if ($unknown = $this->builder->unknownBooks($selection)) {
            abort(404, 'Livre introuvable : '.implode(', ', $unknown));
        }

        // Tout le monde converge vers une seule URL par sélection : les abonnements ne se
        // dédoublent pas et la mise en cache profite à tous.
        if (! $selection->matchesQuery($request)) {
            return redirect()->route('feeds.atom', $selection->toQuery(), 301);
        }

        $entries = $this->builder->entries($selection);
        $self = route('feeds.atom', $selection->toQuery());

        $xml = view('feeds.atom', [
            'entries' => $entries,
            'selfUrl' => $self,
            'title' => $this->title($selection),
            // Un flux vide reste un flux valide : il garde son identité et sa date, sinon les
            // lecteurs le traitent comme disparu et se désabonnent d'eux-mêmes.
            'updated' => $entries->max('updated') ?? now(),
        ])->render();

        return response($xml, 200)
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=900');
    }

    private function title(FeedSelection $selection): string
    {
        if ($selection->isEverything()) {
            return 'Arquanzia';
        }

        $parts = [];

        if ($selection->books !== []) {
            $titles = Book::published()
                ->whereIn('slug', $selection->books)
                ->orderBy('title')
                ->pluck('title')
                ->all();

            $parts = array_merge($parts, $titles);
        }

        foreach ($selection->sections as $section) {
            $parts[] = match ($section) {
                'fil' => 'Fil',
                'encyclopedie' => 'Encyclopédie',
                'fragments' => 'Fragments',
                default => $section,
            };
        }

        return 'Arquanzia — '.implode(', ', $parts);
    }
}
