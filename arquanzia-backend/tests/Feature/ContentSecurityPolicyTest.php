<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La politique de sécurité de contenu est devenue possible avec le rapatriement des assets :
 * tant que Tailwind venait d'un CDN et que sa configuration vivait en scripts intégrés, elle
 * aurait exigé 'unsafe-inline' et l'autorisation d'un domaine tiers — une politique
 * décorative.
 */
class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function policy(string $url = '/'): string
    {
        return $this->get($url)->assertSuccessful()->headers->get('Content-Security-Policy');
    }

    public function test_une_politique_est_posee(): void
    {
        $this->assertNotEmpty($this->policy());
    }

    /** Le point décisif : sans cela, la politique n'empêcherait aucune injection de script. */
    public function test_les_scripts_en_ligne_ne_sont_pas_autorises_en_bloc(): void
    {
        $policy = $this->policy();

        $this->assertStringContainsString("script-src 'self' 'nonce-", $policy);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $policy);
        $this->assertStringNotContainsString('unsafe-eval', $policy);
    }

    public function test_la_politique_interdit_les_sources_tierces_et_l_encadrement(): void
    {
        $policy = $this->policy();

        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("form-action 'self'", $policy);
    }

    public function test_chaque_reponse_recoit_un_nonce_different(): void
    {
        preg_match("/'nonce-([^']+)'/", $this->policy(), $un);
        preg_match("/'nonce-([^']+)'/", $this->policy(), $deux);

        $this->assertNotEmpty($un[1]);
        $this->assertNotSame($un[1], $deux[1], 'Un nonce réutilisé perdrait tout son intérêt.');
    }

    public function test_les_scripts_integres_portent_le_nonce_de_la_reponse(): void
    {
        $response = $this->get('/')->assertSuccessful();

        preg_match("/'nonce-([^']+)'/", $response->headers->get('Content-Security-Policy'), $m);
        $this->assertStringContainsString('nonce="'.$m[1].'"', $response->getContent());
    }

    public function test_le_back_office_est_couvert(): void
    {
        $policy = $this->policy(route('admin.login', absolute: false));

        $this->assertStringContainsString("script-src 'self' 'nonce-", $policy);
    }

    /** Un nonce ne couvre pas les gestionnaires en ligne : il ne doit plus en rester. */
    public function test_aucun_gestionnaire_en_ligne_ne_subsiste_dans_les_gabarits(): void
    {
        $fautifs = [];

        foreach (glob(resource_path('views').'/{,*/,*/*/,*/*/*/}*.blade.php', GLOB_BRACE) as $file) {
            if (preg_match('/\son(click|change|submit|input|load)=/i', file_get_contents($file))) {
                $fautifs[] = str_replace(resource_path('views').'/', '', $file);
            }
        }

        $this->assertSame([], $fautifs, 'Ces gabarits portent un gestionnaire que la CSP bloquerait.');
    }
}
