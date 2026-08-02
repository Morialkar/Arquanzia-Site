<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Toute page profonde doit ramener à sa section.
 *
 * Le site n'a pas de session : un lecteur arrive par un flux, un moteur ou un lien partagé,
 * sans historique de navigation à remonter. Le fil d'Ariane est sa seule indication d'où il
 * se trouve. La fiche d'un livre n'offrait qu'un « ← Retour », les autres sections un fil
 * complet mais rendu de trois façons différentes.
 */
class FilDArianeTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_fiche_d_un_livre_ramene_a_la_bibliotheque(): void
    {
        $book = Book::factory()->create(['slug' => 'les-cendres', 'is_published' => true]);
        Chapter::factory()->for($book)->count(2)->create(['is_published' => true]);

        $this->get(route('library.book', $book->slug))
            ->assertOk()
            ->assertSee(route('library.index'));
    }

    public function test_un_chapitre_ramene_a_son_livre_et_a_la_bibliotheque(): void
    {
        $book = Book::factory()->create(['is_published' => true]);
        $chapters = Chapter::factory()->for($book)->count(2)->create(['is_published' => true]);

        $this->get(route('library.chapter', [$book->slug, $chapters->first()->slug]))
            ->assertOk()
            ->assertSee(route('library.index'))
            ->assertSee('href="'.route('library.book', $book->slug).'"', escape: false);
    }

    /**
     * Un livre d'un seul chapitre n'a pas de fiche : `showBook` redirige vers le chapitre.
     * Le maillon intermédiaire ramènerait donc le lecteur exactement là où il est.
     */
    public function test_un_livre_a_chapitre_unique_n_affiche_pas_de_maillon_vers_lui_meme(): void
    {
        $book = Book::factory()->create(['is_published' => true]);
        $chapter = Chapter::factory()->for($book)->create(['is_published' => true]);

        $this->get(route('library.chapter', [$book->slug, $chapter->slug]))
            ->assertOk()
            ->assertSee(route('library.index'))
            ->assertDontSee('href="'.route('library.book', $book->slug).'"', escape: false);
    }

    public function test_une_entree_d_encyclopedie_liste_ses_ancetres(): void
    {
        $parent = EncyclopediaNode::factory()->category()->create([
            'title' => 'Géographie', 'is_published' => true,
        ]);
        $node = EncyclopediaNode::factory()->create([
            'parent_id' => $parent->id, 'title' => 'Thalria', 'is_published' => true,
        ]);
        EncyclopediaArticle::create(['node_id' => $node->id, 'content_md' => 'Une cité.']);

        $this->get(route('encyclopedia.show', $node->getFullPath()))
            ->assertOk()
            ->assertSee(route('encyclopedia.index'))
            ->assertSee('Géographie');
    }

    public function test_un_fragment_liste_ses_ancetres(): void
    {
        $parent = FragmentNode::factory()->create([
            'type' => 'category', 'title' => 'Carnets', 'is_published' => true,
        ]);
        $node = FragmentNode::factory()->create([
            'parent_id' => $parent->id, 'is_published' => true,
        ]);

        $this->get(route('fragments.show', $node->getFullPath()))
            ->assertOk()
            ->assertSee(route('fragments.index'))
            ->assertSee('Carnets');
    }

    /**
     * Un repère de navigation nommé, et le même séparateur partout : les fragments affichaient
     * « / » et coloraient la page courante comme un lien, l'inverse du reste du site.
     */
    public function test_les_fils_sont_rendus_de_la_meme_facon_partout(): void
    {
        $book = Book::factory()->create(['is_published' => true]);
        $chapters = Chapter::factory()->for($book)->count(2)->create(['is_published' => true]);
        $fragment = FragmentNode::factory()->create(['is_published' => true]);

        foreach ([
            route('library.book', $book->slug),
            route('library.chapter', [$book->slug, $chapters->first()->slug]),
            route('fragments.show', $fragment->getFullPath()),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('aria-label="Fil d’Ariane"', escape: false)
                ->assertDontSee('<span>/</span>', escape: false);
        }
    }
}
