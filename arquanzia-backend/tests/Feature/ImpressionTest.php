<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * L'impression ne se teste pas vraiment sans navigateur : ces vérifications s'assurent que
 * les règles existent et que les éléments à masquer portent bien le repère qui le permet.
 * Le rendu lui-même se contrôle à l'œil, par l'aperçu avant impression.
 */
class ImpressionTest extends TestCase
{
    use RefreshDatabase;

    private function feuilleConstruite(): string
    {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);

        return file_get_contents(public_path('build/'.$manifest['resources/css/app.css']['file']));
    }

    public function test_la_feuille_contient_des_regles_d_impression(): void
    {
        $this->assertStringContainsString('@media print', $this->feuilleConstruite());
    }

    public function test_la_navigation_et_le_decor_sont_masques_a_l_impression(): void
    {
        $css = $this->feuilleConstruite();
        $bloc = substr($css, strpos($css, '@media print'));

        foreach (['data-site-header', 'footer', 'arq-parallax-stars', 'data-reader-controls'] as $cible) {
            $this->assertStringContainsString($cible, $bloc, "{$cible} devrait être masqué à l’impression.");
        }
    }

    /**
     * Masquer tous les `header` emportait aussi celui de l'article, qui porte le titre du
     * chapitre : la page imprimée sortait sans identité. Vu en basculant les règles
     * d'impression à l'écran, ce qu'aucune vérification de code n'aurait montré.
     */
    public function test_seul_l_en_tete_du_site_est_masque_pas_celui_de_l_article(): void
    {
        $css = $this->feuilleConstruite();
        $bloc = substr($css, strpos($css, '@media print'));

        $this->assertStringContainsString('[data-site-header]', $bloc);
        $this->assertDoesNotMatchRegularExpression(
            '/@media print\{[^}]*(^|[,{\s])header\s*[,{]/',
            $bloc,
            'Masquer tous les header emporterait le titre du chapitre.',
        );
    }

    public function test_l_en_tete_du_site_porte_son_repere(): void
    {
        $this->get('/')->assertSuccessful()->assertSee('data-site-header', escape: false);
    }

    /** Sans marge, les paragraphes se collent et le texte imprimé devient un bloc. */
    public function test_les_paragraphes_sont_espaces_a_l_impression(): void
    {
        $css = $this->feuilleConstruite();
        $bloc = substr($css, strpos($css, '@media print'));

        $this->assertStringContainsString('margin-bottom', $bloc);
    }

    /**
     * Le paragraphe visé par une adresse partagée atterrissait derrière les en-têtes collants,
     * qui cumulent près de 145 px : le lien menait à un passage invisible.
     */
    public function test_la_marge_de_defilement_degage_les_en_tetes_collants(): void
    {
        $css = $this->feuilleConstruite();

        preg_match('/p:target\{[^}]*scroll-margin-top:\s*([\d.]+)rem/', $css, $m);

        $this->assertNotEmpty($m, 'p:target doit poser une marge de défilement.');
        $this->assertGreaterThanOrEqual(10, (float) $m[1], 'La marge doit dégager les en-têtes collants.');
    }

    /** Sans ce repère, la règle d'impression n'atteindrait pas les réglages de lecture. */
    public function test_les_reglages_de_lecture_portent_le_repere_d_impression(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->get(route('library.chapter', [$book->slug, $chapter->slug], false))
            ->assertSuccessful()
            ->assertSee('data-reader-controls', escape: false);
    }
}
