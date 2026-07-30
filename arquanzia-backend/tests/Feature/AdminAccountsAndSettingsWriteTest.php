<?php

namespace Tests\Feature;

use App\Models\AdminAllowlist;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsAdmin;
use Tests\TestCase;

/**
 * Écritures sur les comptes, l'allowlist d'administration et les réglages du site.
 *
 * Le téléversement de logo n'est pas couvert ici : Admin\SettingsController::updateLogo()
 * écrit directement sur le disque via UploadedFile::move() et ImageManager::save(), sans
 * passer par la façade Storage. Storage::fake() ne peut donc pas l'intercepter, et un test
 * déposerait de vrais fichiers dans storage/app/public/logos/. À rendre testable en même
 * temps que l'assainissement du SVG prévu au lot 1.2 — d'ici là, seul son contrôle d'accès
 * est vérifié, dans AdminWriteProtectionTest.
 */
class AdminAccountsAndSettingsWriteTest extends TestCase
{
    use ActsAsAdmin, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAdmin();
    }

    public function test_creer_un_utilisateur_fonctionne(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.users.store', absolute: false), [
                'email' => 'lecteur@exemple.test',
                'handle' => 'lecteur',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'lecteur@exemple.test', 'handle' => 'lecteur']);
    }

    public function test_un_courriel_en_double_est_refuse(): void
    {
        User::factory()->create(['email' => 'deja@exemple.test']);

        $this->actingAsAdmin()
            ->post(route('admin.users.store', absolute: false), ['email' => 'deja@exemple.test'])
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'deja@exemple.test')->count());
    }

    /** Seul le root admin peut élargir l'allowlist : c'est la seule barrière à l'escalade. */
    public function test_seul_le_root_admin_peut_ajouter_un_administrateur(): void
    {
        config(['app.root_admin_email' => 'root@arquanzia.test']);

        $this->actingAsAdmin()
            ->post(route('admin.admins.store', absolute: false), [
                'email' => 'complice@exemple.test',
                'role' => 'admin',
            ])
            ->assertSessionHasErrors('permission');

        $this->assertDatabaseMissing('admin_allowlist', ['email' => 'complice@exemple.test']);
    }

    public function test_le_root_admin_peut_ajouter_un_administrateur(): void
    {
        config(['app.root_admin_email' => $this->adminEmail]);

        $this->actingAsAdmin()
            ->post(route('admin.admins.store', absolute: false), [
                'email' => 'Nouvel.Admin@Exemple.test',
                'role' => 'editor',
            ])
            ->assertSessionHasNoErrors();

        // Le contrôleur normalise l'adresse en minuscules avant l'enregistrement.
        $this->assertDatabaseHas('admin_allowlist', ['email' => 'nouvel.admin@exemple.test', 'role' => 'editor']);
    }

    public function test_un_role_d_administrateur_invalide_est_refuse(): void
    {
        config(['app.root_admin_email' => $this->adminEmail]);

        $this->actingAsAdmin()
            ->post(route('admin.admins.store', absolute: false), [
                'email' => 'nouveau@exemple.test',
                'role' => 'superutilisateur',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('admin_allowlist', ['email' => 'nouveau@exemple.test']);
    }

    public function test_retirer_un_administrateur_fonctionne(): void
    {
        config(['app.root_admin_email' => $this->adminEmail]);

        $other = AdminAllowlist::create([
            'email' => 'aretirer@exemple.test',
            'role' => 'editor',
            'created_by_email' => $this->adminEmail,
        ]);

        $this->actingAsAdmin()->delete(route('admin.admins.destroy', $other, false));

        $this->assertDatabaseMissing('admin_allowlist', ['id' => $other->id]);
    }

    public function test_changer_le_nom_du_site_fonctionne(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.settings.name', absolute: false), ['site_name' => 'Arquanzia — Archives'])
            ->assertRedirect();

        $this->assertSame('Arquanzia — Archives', SiteSetting::getSiteName());
    }
}
