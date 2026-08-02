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
 * Le flux RSS annonce les parutions par date de publication : une entrée corrigée ou étoffée
 * n'y apparaît jamais. Cette page comble ce trou.
 */
class RevisionsTest extends TestCase
{
    use RefreshDatabase;

    private function page(): \Illuminate\Testing\TestResponse
    {
        return $this->get(route('revisions', absolute: false));
    }

    // — Ce qui compte comme révision —

    public function test_un_chapitre_revise_apparait(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create(['title' => 'Le Prologue']);

        $chapter->update(['content_md' => 'Un texte entièrement repris.']);

        $this->page()->assertSuccessful()->assertSee('Le Prologue', escape: false);
    }

    /** Une nouveauté n'est pas une révision : le flux l'annonce déjà. */
    public function test_un_texte_jamais_modifie_n_apparait_pas(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['title' => 'Tout Neuf']);

        $this->page()->assertDontSee('Tout Neuf', escape: false);
    }

    /**
     * Le cœur du choix : `updated_at` bouge pour des raisons invisibles au lecteur. Corriger
     * une vignette ne doit pas faire remonter une entrée comme si elle avait été réécrite.
     */
    public function test_un_changement_hors_texte_ne_compte_pas_comme_revision(): void
    {
        $node = EncyclopediaNode::factory()->create(['title' => 'Thalria']);

        $node->update(['order_index' => 5, 'thumbnail_media_id' => null]);

        $this->assertNull($node->fresh()->revised_at);
        $this->page()->assertDontSee('Thalria', escape: false);
    }

    public function test_modifier_le_titre_compte_comme_revision(): void
    {
        $node = EncyclopediaNode::factory()->create(['title' => 'Ancien nom']);

        $node->update(['title' => 'Nouveau nom']);

        $this->page()->assertSee('Nouveau nom', escape: false);
    }

    /** Le corps d'un article vit dans une autre table : sa modification doit compter. */
    public function test_modifier_le_corps_d_un_article_compte_comme_revision(): void
    {
        $node = EncyclopediaNode::factory()->create(['title' => 'Sporaka']);
        $article = EncyclopediaArticle::create(['node_id' => $node->id, 'content_md' => 'Première version.']);

        $this->page()->assertDontSee('Sporaka', escape: false);

        $article->update(['content_md' => 'Version étoffée.']);

        $this->assertNotNull($node->fresh()->revised_at);
        $this->page()->assertSee('Sporaka', escape: false);
    }

    public function test_un_fragment_revise_apparait(): void
    {
        $fragment = FragmentNode::factory()->create(['title' => 'Un croquis']);

        $fragment->update(['description_md' => 'Description retravaillée.']);

        $this->page()->assertSee('Un croquis', escape: false);
    }

    // — Règle de publication —

    public function test_un_texte_non_publie_n_apparait_pas(): void
    {
        $book = Book::factory()->create();
        $brouillon = Chapter::factory()->for($book)->draft()->create(['title' => 'Brouillon Révisé']);
        $brouillon->update(['content_md' => 'Repris en secret.']);

        $node = EncyclopediaNode::factory()->draft()->create(['title' => 'Entrée Cachée']);
        $node->update(['teaser_md' => 'Repris aussi.']);

        $this->page()
            ->assertDontSee('Brouillon Révisé', escape: false)
            ->assertDontSee('Entrée Cachée', escape: false);
    }

    public function test_un_chapitre_d_un_livre_non_publie_n_apparait_pas(): void
    {
        $book = Book::factory()->draft()->create();
        $chapter = Chapter::factory()->for($book)->create(['title' => 'Chapitre Caché']);
        $chapter->update(['content_md' => 'Repris.']);

        $this->page()->assertDontSee('Chapitre Caché', escape: false);
    }

    // — Présentation —

    public function test_les_revisions_sont_classees_de_la_plus_recente_a_la_plus_ancienne(): void
    {
        $book = Book::factory()->create();

        $ancien = Chapter::factory()->for($book)->create(['title' => 'Ancienne reprise']);
        $ancien->update(['content_md' => 'a']);
        $ancien->forceFill(['revised_at' => now()->subMonth()])->saveQuietly();

        $recent = Chapter::factory()->for($book)->create(['title' => 'Reprise récente']);
        $recent->update(['content_md' => 'b']);

        $html = $this->page()->getContent();

        $this->assertLessThan(
            strpos($html, 'Ancienne reprise'),
            strpos($html, 'Reprise récente'),
        );
    }

    public function test_la_page_repond_meme_sans_aucune_revision(): void
    {
        $this->page()->assertSuccessful()->assertSee('Aucun texte', escape: false);
    }

    /** Une section publique doit être atteignable : le fil est resté invisible faute de lien. */
    public function test_la_page_est_liee_depuis_le_site(): void
    {
        $this->get('/')
            ->assertSuccessful()
            ->assertSee('href="'.route('revisions').'"', escape: false);
    }
}
