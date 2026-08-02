<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Wikilink;
use App\Services\MentionIndexer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rétroliens : sur la page d'une entrée, la liste des textes qui la citent.
 *
 * C'est l'inverse du graphe de wikilinks, écarté comme prématuré — et bien plus utile, puisque
 * cela fonctionne dès la deuxième entrée et se lit sans visualisation.
 */
class RetroliensTest extends TestCase
{
    use RefreshDatabase;

    private function cible(string $titre = 'Thalria'): EncyclopediaNode
    {
        return EncyclopediaNode::factory()->create(['title' => $titre]);
    }

    private function lire(EncyclopediaNode $node): \Illuminate\Testing\TestResponse
    {
        return $this->get(route('encyclopedia.show', $node->getFullPath(), false));
    }

    // — Indexation —

    public function test_un_chapitre_qui_cite_une_entree_apparait(): void
    {
        $cible = $this->cible();
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create([
            'title' => 'Le Prologue',
            'content_md' => 'On y parle de [[Thalria]] longuement.',
        ]);

        $this->lire($cible)
            ->assertSuccessful()
            ->assertSee('Mentionné dans', escape: false)
            ->assertSee('Le Prologue', escape: false);
    }

    public function test_une_autre_entree_qui_cite_apparait(): void
    {
        $cible = $this->cible();
        $source = EncyclopediaNode::factory()->create(['title' => 'Dalhia', 'teaser_md' => 'Voisine de [[Thalria]].']);

        $this->lire($cible)->assertSee('Dalhia', escape: false);
        $this->assertDatabaseCount('mentions', 1);
        $this->assertSame($source->id, \App\Models\Mention::first()->source_id);
    }

    public function test_un_fragment_qui_cite_apparait(): void
    {
        $cible = $this->cible();
        FragmentNode::factory()->create(['title' => 'Un croquis', 'description_md' => 'Vue de [[Thalria]].']);

        $this->lire($cible)->assertSee('Un croquis', escape: false);
    }

    public function test_le_corps_d_un_article_est_pris_en_compte(): void
    {
        $cible = $this->cible();
        $source = EncyclopediaNode::factory()->create(['title' => 'Sporaka', 'teaser_md' => null]);
        EncyclopediaArticle::create(['node_id' => $source->id, 'content_md' => 'Reliée à [[Thalria]].']);

        $this->lire($cible)->assertSee('Sporaka', escape: false);
    }

    public function test_la_forme_avec_libelle_est_reconnue(): void
    {
        $cible = $this->cible();
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create([
            'title' => 'Chapitre premier',
            'content_md' => 'On rejoint [[Thalria|les terres hautes]].',
        ]);

        $this->lire($cible)->assertSee('Chapitre premier', escape: false);
    }

    public function test_un_wikilink_declare_mene_a_la_bonne_entree(): void
    {
        $cible = $this->cible('Les Hautes Terres');
        Wikilink::factory()->create([
            'term' => 'Thalria',
            'encyclopedia_node_id' => $cible->id,
            'custom_url' => null,
        ]);

        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['title' => 'Un chapitre', 'content_md' => 'Vers [[Thalria]].']);

        $this->lire($cible)->assertSee('Un chapitre', escape: false);
    }

    /** Un wikilink pointant vers une adresse libre ne vise aucune entrée. */
    public function test_un_wikilink_vers_une_adresse_libre_ne_produit_pas_de_mention(): void
    {
        Wikilink::factory()->create(['term' => 'Ailleurs', 'custom_url' => 'https://exemple.test']);

        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => 'Voir [[Ailleurs]].']);

        $this->assertDatabaseCount('mentions', 0);
    }

    // — Étanchéité —

    /** Un brouillon qui cite une entrée trahirait son existence et son titre. */
    public function test_un_texte_non_publie_n_apparait_pas(): void
    {
        $cible = $this->cible();

        $brouillonLivre = Book::factory()->draft()->create();
        Chapter::factory()->for($brouillonLivre)->create(['title' => 'Chapitre Caché', 'content_md' => '[[Thalria]]']);

        $publie = Book::factory()->create();
        Chapter::factory()->for($publie)->draft()->create(['title' => 'Brouillon Caché', 'content_md' => '[[Thalria]]']);

        EncyclopediaNode::factory()->draft()->create(['title' => 'Entrée Cachée', 'teaser_md' => '[[Thalria]]']);
        FragmentNode::factory()->draft()->create(['title' => 'Fragment Caché', 'description_md' => '[[Thalria]]']);

        $reponse = $this->lire($cible)->assertSuccessful();

        foreach (['Chapitre Caché', 'Brouillon Caché', 'Entrée Cachée', 'Fragment Caché'] as $titre) {
            $reponse->assertDontSee($titre, escape: false);
        }
        $reponse->assertDontSee('Mentionné dans', escape: false);
    }

    public function test_un_chapitre_a_paraitre_n_apparait_pas(): void
    {
        $cible = $this->cible();
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->comingSoon()->create(['title' => 'À Paraître', 'content_md' => '[[Thalria]]']);

        $this->lire($cible)->assertDontSee('À Paraître', escape: false);
    }

    public function test_une_entree_ne_se_cite_pas_elle_meme(): void
    {
        $cible = $this->cible();
        $cible->update(['teaser_md' => 'Voir [[Thalria]].']);

        $this->assertDatabaseCount('mentions', 0);
    }

    // — Tenue à jour —

    public function test_dix_citations_dans_un_meme_texte_ne_donnent_qu_une_mention(): void
    {
        $this->cible();
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => str_repeat('Encore [[Thalria]]. ', 10)]);

        $this->assertDatabaseCount('mentions', 1);
    }

    public function test_retirer_la_citation_retire_le_retrolien(): void
    {
        $cible = $this->cible();
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create(['title' => 'Un chapitre', 'content_md' => '[[Thalria]]']);

        $this->assertDatabaseCount('mentions', 1);

        $chapter->update(['content_md' => 'Plus aucune référence.']);

        $this->assertDatabaseCount('mentions', 0);
        $this->lire($cible)->assertDontSee('Mentionné dans', escape: false);
    }

    public function test_supprimer_le_texte_source_nettoie_l_index(): void
    {
        $this->cible();
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create(['content_md' => '[[Thalria]]']);

        $chapter->delete();

        $this->assertDatabaseCount('mentions', 0);
    }

    public function test_supprimer_l_entree_cible_nettoie_l_index(): void
    {
        $cible = $this->cible();
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => '[[Thalria]]']);

        $cible->delete();

        $this->assertDatabaseCount('mentions', 0);
    }

    public function test_l_index_se_reconstruit(): void
    {
        $this->cible();
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => '[[Thalria]]']);

        \App\Models\Mention::query()->delete();
        $this->assertDatabaseCount('mentions', 0);

        app(MentionIndexer::class)->rebuild();

        $this->assertDatabaseCount('mentions', 1);
    }

    /** L'index existe pour ne pas relire tout le site à chaque affichage. */
    public function test_l_affichage_ne_relit_pas_tout_le_contenu(): void
    {
        $cible = $this->cible();
        $book = Book::factory()->create();

        foreach (range(1, 3) as $i) {
            Chapter::factory()->for($book)->create(['content_md' => "Texte {$i} citant [[Thalria]]."]);
        }
        $petit = $this->compterRequetes(fn () => $this->lire($cible));

        foreach (range(4, 15) as $i) {
            Chapter::factory()->for($book)->create(['content_md' => "Texte {$i} citant [[Thalria]]."]);
        }
        $grand = $this->compterRequetes(fn () => $this->lire($cible));

        // Égalité stricte trop rigide : le compte varie légèrement selon l'état des caches
        // internes. Ce qui compte est l'absence de croissance avec le volume.
        $this->assertLessThanOrEqual(
            $petit,
            $grand,
            "Le nombre de requêtes croît avec le contenu : {$petit} puis {$grand}.",
        );
    }

    private function compterRequetes(callable $action): int
    {
        \Illuminate\Support\Facades\DB::flushQueryLog();
        \Illuminate\Support\Facades\DB::enableQueryLog();
        $action();
        $n = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();

        return $n;
    }
}
