<?php

namespace Tests\Feature;

use App\Models\AdminAllowlist;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use App\Models\User;
use App\Models\Wikilink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rend chaque écran du back-office en session administrateur.
 *
 * C'est le test qui manquait : /admin/users et /admin/users/{id} ont été livrés en erreur 500
 * parce que les gabarits lisaient des attributs supprimés du modèle. Aucune vérification
 * statique ne voit ce défaut — il faut rendre la page.
 */
class AdminPagesSmokeTest extends TestCase
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

    private function actingAsAdmin(): static
    {
        return $this->withSession([
            'admin_email' => self::ADMIN_EMAIL,
            'admin_role' => 'admin',
        ]);
    }

    public function test_tous_les_ecrans_du_back_office_repondent(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create();
        $node = EncyclopediaNode::factory()->create();
        $fragment = FragmentNode::factory()->create();
        $wikilink = Wikilink::factory()->create();

        $urls = [
            route('admin.dashboard', absolute: false),
            route('admin.admins.index', absolute: false),
            route('admin.analytics.index', absolute: false),
            route('admin.audit.index', absolute: false),
            route('admin.settings.index', absolute: false),

            route('admin.posts.index', absolute: false),
            route('admin.posts.create', absolute: false),
            route('admin.posts.edit', $post, false),

            route('admin.users.index', absolute: false),
            route('admin.users.create', absolute: false),
            route('admin.users.show', $user, false),

            route('admin.books.index', absolute: false),
            route('admin.books.create', absolute: false),
            route('admin.books.edit', $book, false),
            route('admin.chapters.create', $book, false),
            route('admin.chapters.edit', [$book, $chapter], false),

            route('admin.encyclopedia.index', absolute: false),
            route('admin.encyclopedia.create', absolute: false),
            route('admin.encyclopedia.edit', $node, false),
            route('admin.encyclopedia.import', absolute: false),

            route('admin.fragments.index', absolute: false),
            route('admin.fragments.create', absolute: false),
            route('admin.fragments.edit', $fragment, false),

            route('admin.wikilinks.index', absolute: false),
            route('admin.wikilinks.create', absolute: false),
            route('admin.wikilinks.edit', $wikilink, false),
        ];

        foreach ($urls as $url) {
            $this->actingAsAdmin()->get($url)->assertSuccessful();
        }
    }

    public function test_la_page_de_connexion_est_accessible_sans_session(): void
    {
        $this->get(route('admin.login', absolute: false))->assertSuccessful();
    }
}
