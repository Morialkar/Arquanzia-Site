<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyndicationFeedTest extends TestCase
{
    use RefreshDatabase;

    private function atom(array $query = []): \Illuminate\Testing\TestResponse
    {
        return $this->get(route('feeds.atom', $query, false));
    }

    // — Bases —

    public function test_le_flux_est_un_atom_valide(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create();

        $response = $this->atom()->assertSuccessful();

        $this->assertStringStartsWith('application/atom+xml', $response->headers->get('Content-Type'));

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'Le flux doit être du XML bien formé.');
        $this->assertSame('feed', $xml->getName());
    }

    public function test_le_flux_porte_un_lien_self_et_un_identifiant_stables(): void
    {
        $premier = $this->atom()->getContent();
        $second = $this->atom()->getContent();

        $extraire = fn (string $xml) => (string) simplexml_load_string($xml)->id;

        $this->assertSame($extraire($premier), $extraire($second));
        $this->assertStringContainsString('rel="self"', $premier);
    }

    /** Un 404 pousserait les lecteurs à se désabonner d'eux-mêmes. */
    public function test_un_flux_sans_entree_reste_valide(): void
    {
        $response = $this->atom()->assertSuccessful();

        $xml = simplexml_load_string($response->getContent());
        $this->assertCount(0, $xml->entry);
        $this->assertNotEmpty((string) $xml->updated);
    }

    public function test_le_flux_est_mis_en_cache(): void
    {
        $this->atom()->assertHeader('Cache-Control', 'max-age=900, public');
    }

    // — Contenu —

    public function test_un_chapitre_publie_figure_avec_son_texte_integral(): void
    {
        $book = Book::factory()->create(['title' => 'Les Cendres']);
        Chapter::factory()->for($book)->create([
            'title' => 'Ouverture',
            'content_md' => 'Un paragraphe reconnaissable entre mille.',
        ]);

        $this->atom()
            ->assertSee('Ouverture — Les Cendres', escape: false)
            ->assertSee('Un paragraphe reconnaissable entre mille.', escape: false);
    }

    public function test_le_bandeau_promotionnel_accompagne_le_chapitre(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create([
            'content_md' => 'Le texte.',
            'promo_banner_enabled' => true,
            'promo_banner_text' => 'Le tome papier est disponible.',
            'promo_banner_button_label' => 'Commander',
            'promo_banner_button_url' => 'https://example.test/commander',
        ]);

        $this->atom()
            ->assertSee('Le tome papier est disponible.', escape: false)
            ->assertSee('https://example.test/commander', escape: false);
    }

    public function test_les_adresses_du_contenu_sont_absolues(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create([
            'content_md' => 'Une image : <img src="/media/exemple.jpg"> et un [lien](/bibliotheque).',
        ]);

        $content = $this->atom()->getContent();

        $this->assertStringNotContainsString('src="/media', $content, 'Hors du site, un chemin relatif ne mène nulle part.');
        $this->assertStringContainsString(url('/media/exemple.jpg'), $content);
    }

    // — Règle de publication —

    public function test_un_chapitre_non_publie_est_absent(): void
    {
        $book = Book::factory()->create();
        $brouillon = Chapter::factory()->for($book)->draft()->create(['title' => 'Brouillon Secret']);

        $this->atom()->assertDontSee($brouillon->slug);
    }

    public function test_un_chapitre_a_paraitre_est_absent(): void
    {
        $book = Book::factory()->create();
        $futur = Chapter::factory()->for($book)->comingSoon()->create(['title' => 'À Paraître']);

        $this->atom()->assertDontSee($futur->slug);
    }

    public function test_un_chapitre_d_un_livre_non_publie_est_absent(): void
    {
        $book = Book::factory()->draft()->create();
        $chapitre = Chapter::factory()->for($book)->create();

        $this->atom()->assertDontSee($chapitre->slug);
    }

    public function test_un_brouillon_d_encyclopedie_est_absent(): void
    {
        $node = EncyclopediaNode::factory()->draft()->create();

        $this->atom(['sections' => 'encyclopedie'])->assertDontSee($node->slug);
    }

    // — Sélection —

    public function test_un_flux_de_livre_ne_contient_que_ce_livre(): void
    {
        $suivi = Book::factory()->create(['slug' => 'suivi']);
        $autre = Book::factory()->create(['slug' => 'autre']);
        $chapitreSuivi = Chapter::factory()->for($suivi)->create();
        $chapitreAutre = Chapter::factory()->for($autre)->create();

        $this->atom(['livres' => 'suivi'])
            ->assertSee($chapitreSuivi->slug)
            ->assertDontSee($chapitreAutre->slug);
    }

    public function test_plusieurs_livres_peuvent_etre_suivis(): void
    {
        $a = Book::factory()->create(['slug' => 'aaa']);
        $b = Book::factory()->create(['slug' => 'bbb']);
        $chapA = Chapter::factory()->for($a)->create();
        $chapB = Chapter::factory()->for($b)->create();

        $this->atom(['livres' => 'aaa,bbb'])
            ->assertSee($chapA->slug)
            ->assertSee($chapB->slug);
    }

    public function test_une_section_peut_etre_suivie_seule(): void
    {
        $book = Book::factory()->create();
        $chapitre = Chapter::factory()->for($book)->create();
        $billet = Post::factory()->create(['title' => 'Une annonce']);

        $this->atom(['sections' => 'fil'])
            ->assertSee('Une annonce', escape: false)
            ->assertDontSee($chapitre->slug);
    }

    public function test_les_fragments_peuvent_etre_suivis(): void
    {
        $fragment = FragmentNode::factory()->create(['title' => 'Un croquis']);

        $this->atom(['sections' => 'fragments'])->assertSee($fragment->slug);
    }

    public function test_sans_parametre_le_flux_couvre_tout(): void
    {
        $book = Book::factory()->create();
        $chapitre = Chapter::factory()->for($book)->create();
        Post::factory()->create(['title' => 'Annonce Générale']);

        $this->atom()
            ->assertSee($chapitre->slug)
            ->assertSee('Annonce Générale', escape: false);
    }

    // — Forme canonique —

    public function test_un_ordre_different_redirige_vers_la_forme_canonique(): void
    {
        Book::factory()->create(['slug' => 'aaa']);
        Book::factory()->create(['slug' => 'bbb']);

        $this->get(route('feeds.atom', ['livres' => 'bbb,aaa'], false))
            ->assertRedirect(route('feeds.atom', ['livres' => 'aaa,bbb']))
            ->assertStatus(301);
    }

    public function test_les_doublons_et_la_casse_sont_normalises(): void
    {
        Book::factory()->create(['slug' => 'aaa']);

        $this->get(route('feeds.atom', ['livres' => 'AAA,aaa'], false))
            ->assertRedirect(route('feeds.atom', ['livres' => 'aaa']));
    }

    public function test_la_forme_canonique_ne_redirige_pas(): void
    {
        Book::factory()->create(['slug' => 'aaa']);

        $this->atom(['livres' => 'aaa'])->assertSuccessful();
    }

    public function test_une_section_inconnue_est_ignoree(): void
    {
        $this->get(route('feeds.atom', ['sections' => 'inexistante'], false))
            ->assertRedirect(route('feeds.atom'));
    }

    // — Garde-fous —

    /** Un flux muet ne se signale jamais : mieux vaut une erreur franche. */
    public function test_un_livre_inconnu_est_refuse(): void
    {
        $this->atom(['livres' => 'ce-livre-nexiste-pas'])->assertNotFound();
    }

    public function test_un_livre_non_publie_est_traite_comme_inconnu(): void
    {
        $book = Book::factory()->draft()->create(['slug' => 'cache']);

        $this->atom(['livres' => $book->slug])->assertNotFound();
    }

    public function test_le_nombre_de_livres_est_plafonne(): void
    {
        $slugs = collect(range(1, 25))->map(fn ($i) => 'livre-'.$i);

        $this->atom(['livres' => $slugs->implode(',')])->assertStatus(400);
    }

    public function test_le_flux_est_limite_en_nombre_d_entrees(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->count(25)->create();

        $xml = simplexml_load_string($this->atom()->getContent());

        $this->assertLessThanOrEqual(20, count($xml->entry));
    }

    // — Page de composition —

    public function test_la_page_de_composition_repond(): void
    {
        Book::factory()->create(['title' => 'Les Cendres']);

        $this->get(route('feeds.builder', absolute: false))
            ->assertSuccessful()
            ->assertSee('Les Cendres', escape: false)
            ->assertSee(route('feeds.atom'), escape: false);
    }

    public function test_le_flux_est_annonce_dans_les_pages_publiques(): void
    {
        $this->get('/')
            ->assertSuccessful()
            ->assertSee('application/atom+xml', escape: false)
            ->assertSee(route('feeds.atom'), escape: false);
    }
}
