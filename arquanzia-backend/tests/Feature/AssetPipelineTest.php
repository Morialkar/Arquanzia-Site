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
}
