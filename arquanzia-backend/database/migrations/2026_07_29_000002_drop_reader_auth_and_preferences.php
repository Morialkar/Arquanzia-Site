<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire le socle d'authentification lecteur, qui n'a jamais été branché, et les préférences
 * de lecture côté serveur, qui n'ont jamais été lues.
 *
 * - `webauthn_credentials` et `remember_logins` : tables d'un login lecteur abandonné. Aucune
 *   route, aucun contrôleur ne s'en servait.
 * - `users.password` : ajoutée pour ce même login, jamais renseignée. L'accès au back-office
 *   passe par un lien magique.
 * - `users.reader_font`, `users.reader_font_size` : le front gère la police et sa taille
 *   entièrement en localStorage, l'endpoint serveur n'était jamais appelé.
 * - `users.theme_pref` : idem, le thème clair/sombre vit en localStorage.
 * - `users.notification_prefs` : vestige des notifications, supprimées avec la refonte.
 *
 * Chaque opération est gardée : la migration doit passer aussi bien sur la base de production,
 * où ces objets existent, que sur une base neuve montée à partir de zéro pour les tests.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    protected array $userColumns = [
        'password',
        'reader_font',
        'reader_font_size',
        'theme_pref',
        'notification_prefs',
    ];

    public function up(): void
    {
        Schema::dropIfExists('webauthn_credentials');
        Schema::dropIfExists('remember_logins');

        $existing = array_values(array_filter(
            $this->userColumns,
            fn (string $column) => Schema::hasColumn('users', $column),
        ));

        if ($existing !== []) {
            Schema::table('users', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }

    /**
     * Restitue les colonnes de `users` pour permettre un retour arrière. Les deux tables du
     * login abandonné ne sont pas recréées : leur schéma vit dans l'historique git, et rien
     * dans le code ne saurait plus quoi en faire.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable();
            }
            if (! Schema::hasColumn('users', 'reader_font')) {
                $table->string('reader_font')->nullable();
            }
            if (! Schema::hasColumn('users', 'reader_font_size')) {
                $table->unsignedSmallInteger('reader_font_size')->nullable();
            }
            if (! Schema::hasColumn('users', 'theme_pref')) {
                $table->string('theme_pref')->nullable();
            }
            if (! Schema::hasColumn('users', 'notification_prefs')) {
                $table->json('notification_prefs')->nullable();
            }
        });
    }
};
