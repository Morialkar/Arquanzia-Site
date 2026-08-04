<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verrouille le rapatriement des assets.
 *
 * Le site chargeait Tailwind depuis un CDN, avec sa configuration en scripts intégrés, alors
 * que Vite était installé et inutilisé. Outre le coût — compilation du CSS dans le navigateur
 * à chaque page — cela rendait toute politique de sécurité de contenu illusoire, puisqu'il
 * fallait autoriser un domaine tiers et l'exécution de scripts en ligne.
 */
class AssetPipelineTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return [
            'accueil' => ['/'],
            'fil' => ['/fil'],
            'bibliothèque' => ['/bibliotheque'],
            'encyclopédie' => ['/encyclopedie'],
            'fragments' => ['/fragments'],
            'composition de flux' => ['/flux'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function test_aucune_page_ne_charge_de_ressource_externe(string $url): void
    {
        $html = $this->get($url)->assertSuccessful()->getContent();

        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringNotContainsString('fonts.gstatic.com', $html);
    }

    public function test_les_pages_publiques_servent_les_assets_construits(): void
    {
        $html = $this->get('/')->assertSuccessful()->getContent();

        $this->assertStringContainsString('/build/assets/', $html, 'Le manifeste Vite doit être servi.');
    }

    public function test_le_back_office_sert_les_memes_assets(): void
    {
        $html = $this->get(route('admin.login', absolute: false))->assertSuccessful()->getContent();

        $this->assertStringContainsString('/build/assets/', $html);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
    }

    public function test_les_polices_sont_auto_hebergees(): void
    {
        foreach ([
            'public/fonts/cormorant-garamond/cormorant-garamond-normal-latin.woff2',
            'public/fonts/cormorant-garamond/cormorant-garamond-italic-latin.woff2',
            'public/fonts/inter/inter-normal-latin.woff2',
        ] as $font) {
            $this->assertFileExists(base_path($font));
        }
    }

    /** La configuration du thème doit vivre dans la feuille construite, plus dans le gabarit. */
    public function test_le_theme_est_compile_dans_la_feuille_de_style(): void
    {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $css = file_get_contents(public_path('build/'.$manifest['resources/css/app.css']['file']));

        $this->assertStringContainsString('bg-arq-mint', $css);
        $this->assertStringContainsString('shadow-parchment', $css);
        $this->assertStringContainsString('.dark\:bg-arq-night', $css, 'La variante sombre doit être générée.');
    }

    /**
     * Les en-têtes épinglés doivent s'empiler, non se recouvrir.
     *
     * La mise en forme visait « header » sans distinction : l'en-tête d'un chapitre s'épinglait
     * à top:0, donc derrière la barre du site, et celui d'un article d'encyclopédie devenait
     * collant alors que son balisage ne le demandait pas.
     */
    public function test_seule_la_barre_du_site_est_epinglee_en_haut(): void
    {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $css = file_get_contents(public_path('build/'.$manifest['resources/css/app.css']['file']));

        $sansEspaces = preg_replace('/\s+/', '', $css);

        // Le minifieur réordonne les propriétés : ne rien supposer de leur ordre.
        $this->assertDoesNotMatchRegularExpression('/(^|[,}])header\{[^}]*position:sticky/', $sansEspaces,
            'La mise en forme épinglée ne doit viser que la barre du site.');
        $this->assertMatchesRegularExpression('/\[data-site-header\]\{[^}]*position:sticky/', $sansEspaces);
        $this->assertMatchesRegularExpression(
            '/\.arq-entete-lecture\{[^}]*top:var\(--arq-decalage-site/', $sansEspaces,
            'Un en-tête de lecture se cale sous la barre du site, dont la hauteur est mesurée.',
        );
    }

    /**
     * Le corps de texte doit garder son rythme.
     *
     * Le greffon de typographie n'est pas installé : la classe « prose » ne met rien en forme
     * d'elle-même, et le reset de Tailwind annule marges et puces. Sans les règles écrites à la
     * main, deux paragraphes séparés par une ligne vide se collaient — le saut ne se voyait pas
     * — et les titres se confondaient avec le texte courant.
     */
    public function test_le_corps_de_texte_garde_ses_marges_et_ses_puces(): void
    {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $css = file_get_contents(public_path('build/'.$manifest['resources/css/app.css']['file']));

        $sansEspaces = preg_replace('/\s+/', '', $css);

        $this->assertStringContainsString('.prosep', $sansEspaces, 'Les paragraphes doivent être espacés.');
        $this->assertMatchesRegularExpression('/\.prosep[^{]*\{[^}]*margin/', $sansEspaces);
        $this->assertMatchesRegularExpression('/\.proseul[^{]*\{[^}]*list-style/', $sansEspaces);
        $this->assertMatchesRegularExpression('/\.proseh2[^{]*\{[^}]*font-size/', $sansEspaces);
    }
}
