<?php

namespace Tests\Feature;

use App\Models\EncyclopediaNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncyclopediaRandomTest extends TestCase
{
    use RefreshDatabase;

    private function tirer(): \Illuminate\Testing\TestResponse
    {
        return $this->get(route('encyclopedia.random', absolute: false));
    }

    public function test_le_tirage_mene_a_un_article_publie(): void
    {
        $node = EncyclopediaNode::factory()->create(['title' => 'Thalria']);

        $this->tirer()->assertRedirect(route('encyclopedia.show', $node->getFullPath()));
    }

    /**
     * Le motif « .* » de la route d'affichage avalerait « au-hasard » et le traiterait comme
     * le chemin d'un nœud : l'ordre de déclaration est ce qui l'en empêche.
     */
    public function test_l_adresse_n_est_pas_avalee_par_la_route_attrape_tout(): void
    {
        EncyclopediaNode::factory()->create();

        $this->tirer()->assertRedirect();
        $this->tirer()->assertStatus(302);
    }

    public function test_un_brouillon_n_est_jamais_tire(): void
    {
        $publie = EncyclopediaNode::factory()->create(['title' => 'Visible']);
        EncyclopediaNode::factory()->draft()->count(10)->create();

        foreach (range(1, 15) as $i) {
            $this->tirer()->assertRedirect(route('encyclopedia.show', $publie->getFullPath()));
        }
    }

    /** Une catégorie n'a pas de contenu propre : y atterrir serait une déception. */
    public function test_une_categorie_n_est_jamais_tiree(): void
    {
        $article = EncyclopediaNode::factory()->create(['title' => 'Un article']);
        EncyclopediaNode::factory()->category()->count(10)->create();

        foreach (range(1, 15) as $i) {
            $this->tirer()->assertRedirect(route('encyclopedia.show', $article->getFullPath()));
        }
    }

    public function test_une_encyclopedie_vide_renvoie_a_l_index_sans_erreur(): void
    {
        $this->tirer()->assertRedirect(route('encyclopedia.index'));
    }

    public function test_le_bouton_est_propose_quand_il_y_a_du_contenu(): void
    {
        EncyclopediaNode::factory()->create();

        $this->get('/encyclopedie')
            ->assertSuccessful()
            ->assertSee(route('encyclopedia.random'), escape: false)
            ->assertSee('Une entrée au hasard', escape: false);
    }

    public function test_le_bouton_est_absent_quand_l_encyclopedie_est_vide(): void
    {
        $this->get('/encyclopedie')
            ->assertSuccessful()
            ->assertDontSee('Une entrée au hasard', escape: false);
    }
}
