<?php

namespace Tests\Feature;

use App\Models\AdminAllowlist;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_EMAIL = 'admin@arquanzia.test';

    protected function setUp(): void
    {
        parent::setUp();

        AdminAllowlist::create([
            'email' => self::ADMIN_EMAIL,
            'role' => 'admin',
            'created_by_email' => self::ADMIN_EMAIL,
        ]);
    }

    public function test_le_back_office_est_inaccessible_sans_session(): void
    {
        $this->get(route('admin.dashboard', absolute: false))
            ->assertRedirect(route('admin.login'));
    }

    /**
     * Verrouille un bug corrigé : logout() n'oubliait que admin_email et admin_role en laissant
     * user_id, que le middleware AdminAuth re-promeut automatiquement s'il est dans l'allowlist.
     * La déconnexion ne déconnectait donc pas.
     */
    public function test_apres_deconnexion_le_back_office_redirige_vers_la_connexion(): void
    {
        $user = User::factory()->create(['email' => self::ADMIN_EMAIL]);

        $this->withSession(['admin_email' => self::ADMIN_EMAIL, 'user_id' => $user->id])
            ->post(route('admin.logout', absolute: false))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.dashboard', absolute: false))
            ->assertRedirect(route('admin.login'));
    }

    public function test_la_deconnexion_vide_entierement_la_session(): void
    {
        $user = User::factory()->create(['email' => self::ADMIN_EMAIL]);

        $this->withSession(['admin_email' => self::ADMIN_EMAIL, 'user_id' => $user->id])
            ->post(route('admin.logout', absolute: false));

        $this->assertGuest();
        $this->assertNull(session('admin_email'));
        $this->assertNull(session('admin_role'));
        $this->assertNull(session('user_id'), 'user_id doit disparaître, sinon AdminAuth re-promeut la session.');
    }

    public function test_un_lien_magique_valide_connecte_et_regenere_la_session(): void
    {
        $result = MagicLink::createForEmail(self::ADMIN_EMAIL);

        $this->startSession();
        $idAvant = session()->getId();

        $this->get(route('admin.magic', ['token' => $result['token']], false))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame(self::ADMIN_EMAIL, session('admin_email'));
        $this->assertNotSame($idAvant, session()->getId(), 'La session doit être régénérée pour éviter la fixation.');
    }

    public function test_un_lien_magique_est_a_usage_unique(): void
    {
        $result = MagicLink::createForEmail(self::ADMIN_EMAIL);
        $url = route('admin.magic', ['token' => $result['token']], false);

        $this->get($url)->assertRedirect(route('admin.dashboard'));

        $this->flushSession();
        $this->get($url)->assertRedirect(route('admin.login'));
    }

    public function test_un_lien_magique_expire_est_refuse(): void
    {
        $result = MagicLink::createForEmail(self::ADMIN_EMAIL);
        $result['link']->update(['expires_at' => now()->subMinute()]);

        $this->get(route('admin.magic', ['token' => $result['token']], false))
            ->assertRedirect(route('admin.login'));
    }

    public function test_un_jeton_inconnu_est_refuse(): void
    {
        $this->get(route('admin.magic', ['token' => str_repeat('a', 64)], false))
            ->assertRedirect(route('admin.login'));
    }

    public function test_une_adresse_hors_allowlist_ne_recoit_pas_de_lien(): void
    {
        Mail::fake();

        $this->post(route('admin.login.send', absolute: false), ['email' => 'inconnu@exemple.test'])
            ->assertRedirect();

        Mail::assertNothingSent();
        $this->assertDatabaseCount('magic_links', 0);
    }

    public function test_une_adresse_autorisee_recoit_un_lien(): void
    {
        Mail::fake();

        $this->post(route('admin.login.send', absolute: false), ['email' => self::ADMIN_EMAIL])
            ->assertRedirect();

        $this->assertDatabaseCount('magic_links', 1);
    }
}
