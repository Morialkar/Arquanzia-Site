<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La recherche n'interrogeait que les titres, et seulement ceux des livres, chapitres et
 * entrées d'encyclopédie : chercher un mot du texte ne donnait rien, et le fil comme les
 * fragments étaient introuvables.
 */
class SearchTest extends TestCase
{
    use RefreshDatabase;

    private function search(string $q): \Illuminate\Support\Collection
    {
        return app(SearchService::class)->search($q);
    }

    // — Recherche dans le contenu —

    public function test_un_mot_du_texte_d_un_chapitre_est_trouve(): void
    {
        $book = Book::factory()->create(['title' => 'Un livre']);
        Chapter::factory()->for($book)->create([
            'title' => 'Un chapitre',
            'content_md' => 'On y croise un varengoth au détour du sentier.',
        ]);

        $resultats = $this->search('varengoth');

        $this->assertCount(1, $resultats);
        $this->assertSame('chapter', $resultats->first()['type']);
    }

    public function test_un_mot_du_texte_d_un_article_est_trouve(): void
    {
        $node = EncyclopediaNode::factory()->create(['title' => 'Une entrée']);
        EncyclopediaArticle::create(['node_id' => $node->id, 'content_md' => 'Le sel de brume y abonde.']);

        $this->assertCount(1, $this->search('sel de brume'));
    }

    public function test_la_description_d_un_livre_est_fouillee(): void
    {
        Book::factory()->create(['title' => 'Titre neutre', 'description_md' => 'Une saga crépusculaire.']);

        $this->assertCount(1, $this->search('crépusculaire'));
    }

    // — Sections jusqu'ici absentes —

    public function test_les_billets_du_fil_sont_cherchables(): void
    {
        Post::factory()->create(['title' => 'Annonce', 'content_full' => 'La parution est repoussée.']);

        $resultats = $this->search('repoussée');

        $this->assertCount(1, $resultats);
        $this->assertSame('post', $resultats->first()['type']);
    }

    public function test_les_fragments_sont_cherchables(): void
    {
        FragmentNode::factory()->create(['title' => 'Un croquis', 'description_md' => 'Étude de bestiaire.']);

        $resultats = $this->search('bestiaire');

        $this->assertCount(1, $resultats);
        $this->assertSame('fragment', $resultats->first()['type']);
    }

    // — Classement et extraits —

    public function test_une_correspondance_de_titre_passe_avant_une_correspondance_de_texte(): void
    {
        $book = Book::factory()->create(['title' => 'Un livre']);
        Chapter::factory()->for($book)->create([
            'title' => 'Chapitre banal',
            'content_md' => 'Il évoque Thalria en passant.',
        ]);
        $node = EncyclopediaNode::factory()->create(['title' => 'Thalria']);

        $resultats = $this->search('Thalria');

        $this->assertSame($node->title, $resultats->first()['title']);
    }

    public function test_un_resultat_trouve_dans_le_texte_montre_le_passage(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create([
            'title' => 'Titre sans rapport',
            'content_md' => str_repeat('Du remplissage. ', 30).'Le mot rarissime apparaît ici.'.str_repeat(' Encore.', 30),
        ]);

        $extrait = $this->search('rarissime')->first()['excerpt'];

        $this->assertNotNull($extrait, 'Sans extrait, rien n’explique pourquoi ce résultat est là.');
        $this->assertStringContainsString('rarissime', $extrait);
        $this->assertLessThan(300, mb_strlen($extrait));
    }

    public function test_un_resultat_trouve_par_le_titre_montre_le_debut_du_texte(): void
    {
        // « Encyclopédie · Thalria » sans un mot de plus n'apprend rien : le lecteur doit ouvrir
        // la page pour savoir si c'est un lieu, une personne ou une langue.
        EncyclopediaNode::factory()->create([
            'title' => 'Thalria',
            'teaser_md' => 'Cité portuaire du sud, bâtie sur des pilotis.',
        ]);

        $this->assertStringContainsString(
            'Cité portuaire',
            $this->search('Thalria')->first()['excerpt'],
        );
    }

    public function test_le_chapeau_prime_sur_le_corps_pour_presenter_une_entree(): void
    {
        // Le chapeau présente l'entrée ; l'article, lui, entre dans le sujet sans le présenter.
        $node = EncyclopediaNode::factory()->create([
            'title' => 'Thalria',
            'teaser_md' => 'Cité portuaire du sud.',
        ]);
        EncyclopediaArticle::create([
            'node_id' => $node->id,
            'content_md' => 'Fondée sur la vase, elle brûla deux fois.',
        ]);

        $this->assertSame('Cité portuaire du sud.', $this->search('Thalria')->first()['excerpt']);
    }

    public function test_un_resultat_sans_texte_n_affiche_pas_d_extrait_vide(): void
    {
        EncyclopediaNode::factory()->create(['title' => 'Thalria', 'teaser_md' => null]);

        $this->assertNull($this->search('Thalria')->first()['excerpt']);
    }

    public function test_un_chapitre_indique_son_livre(): void
    {
        $book = Book::factory()->create(['title' => 'Les Cendres']);
        Chapter::factory()->for($book)->create(['title' => 'Ouverture']);

        $this->assertSame('Les Cendres', $this->search('Ouverture')->first()['context']);
    }

    // — Règle de publication —

    public function test_le_contenu_non_publie_reste_introuvable(): void
    {
        $brouillonLivre = Book::factory()->draft()->create(['title' => 'Livre Secret']);
        Chapter::factory()->for($brouillonLivre)->create(['title' => 'Chapitre Secret']);

        $publie = Book::factory()->create();
        Chapter::factory()->for($publie)->draft()->create(['title' => 'Chapitre Secret Deux']);

        EncyclopediaNode::factory()->draft()->create(['title' => 'Entrée Secrète']);
        FragmentNode::factory()->draft()->create(['title' => 'Fragment Secret']);

        $this->assertCount(0, $this->search('Secret'));
        $this->assertCount(0, $this->search('Secrète'));
    }

    public function test_un_chapitre_a_paraitre_reste_introuvable(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->comingSoon()->create(['title' => 'Chapitre Futur']);

        $this->assertCount(0, $this->search('Futur'));
    }

    // — Garde-fous —

    public function test_une_requete_trop_courte_ne_cherche_rien(): void
    {
        Book::factory()->create(['title' => 'Arquanzia']);

        $this->assertCount(0, $this->search('A'));
        $this->assertCount(0, $this->search(''));
        $this->assertCount(0, $this->search('   '));
    }

    /**
     * `%` et `_` sont des jokers SQL. Sans échappement, chercher « % » renvoyait tout le site,
     * et « 100% » n'importe quoi.
     */
    public function test_les_jokers_sql_sont_traites_comme_du_texte(): void
    {
        EncyclopediaNode::factory()->create(['title' => 'Une entrée quelconque']);
        EncyclopediaNode::factory()->create(['title' => 'Remise de 100% sur le tome']);

        $this->assertCount(0, $this->search('%'), 'Le joker ne doit pas tout ramener.');
        $this->assertCount(0, $this->search('%%'));
        $this->assertCount(0, $this->search('__'));

        $cent = $this->search('100%');
        $this->assertCount(1, $cent);
        $this->assertSame('Remise de 100% sur le tome', $cent->first()['title']);
    }

    public function test_un_antislash_ne_casse_pas_la_requete(): void
    {
        EncyclopediaNode::factory()->create(['title' => 'Chemin']);

        $this->assertCount(0, $this->search('\\'));
    }

    /** Le caractère d'échappement lui-même ne doit pas être interprété. */
    public function test_le_caractere_d_echappement_est_cherchable(): void
    {
        EncyclopediaNode::factory()->create(['title' => 'Attention !']);
        EncyclopediaNode::factory()->create(['title' => 'Sans ponctuation']);

        $resultats = $this->search('n !');

        $this->assertCount(1, $resultats);
        $this->assertSame('Attention !', $resultats->first()['title']);
    }

    public function test_la_recherche_ignore_la_casse(): void
    {
        EncyclopediaNode::factory()->create(['title' => 'Thalria']);

        $this->assertCount(1, $this->search('thalria'));
        $this->assertCount(1, $this->search('THALRIA'));
    }

    // — Surfaces HTTP —

    public function test_la_page_de_resultats_affiche_les_correspondances(): void
    {
        $node = EncyclopediaNode::factory()->create(['title' => 'Thalria']);

        $this->get('/recherche?q=Thalria')
            ->assertSuccessful()
            ->assertSee('Thalria', escape: false)
            ->assertSee($node->slug);
    }

    public function test_la_page_de_resultats_annonce_l_absence_de_correspondance(): void
    {
        $this->get('/recherche?q=introuvable')
            ->assertSuccessful()
            ->assertSee('Aucun résultat', escape: false);
    }

    public function test_l_api_renvoie_les_memes_correspondances(): void
    {
        EncyclopediaNode::factory()->create(['title' => 'Thalria']);

        $this->get('/api/recherche?q=Thalria')
            ->assertSuccessful()
            ->assertJsonCount(1)
            ->assertJsonFragment(['type' => 'encyclopedia', 'title' => 'Thalria']);
    }

    public function test_l_api_est_bornee_en_nombre_de_resultats(): void
    {
        EncyclopediaNode::factory()->count(15)->create(['is_published' => true]);
        EncyclopediaNode::factory()->count(15)->sequence(fn ($s) => ['title' => 'Commun '.$s->index])->create();

        $this->get('/api/recherche?q=Commun')
            ->assertSuccessful()
            ->assertJsonCount(8);
    }
}
