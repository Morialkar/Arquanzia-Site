<?php

namespace Tests\Feature;

use App\Models\AdminAllowlist;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaGalleryImage;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Wikilink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsAdmin;
use Tests\TestCase;

/**
 * Balaie TOUS les points d'écriture protégés du back-office et vérifie qu'aucun n'est
 * accessible sans session administrateur.
 *
 * C'était le trou le plus large de la couverture : vingt-sept verbes d'écriture derrière le
 * middleware admin.auth, aucun test. Une inversion de condition ou un oubli de middleware sur
 * une seule route exposerait la modification ou la destruction de contenu à n'importe qui.
 *
 * Sur la CSRF : elle n'est pas testable ici. Le middleware ValidateCsrfToken de Laravel
 * court-circuite sa vérification quand runningUnitTests() est vrai, ce qui est le cas de
 * toute la suite. Un test écrit malgré tout passerait à vide et donnerait une fausse
 * assurance. La protection reste active en production, où la condition est fausse.
 */
class AdminWriteProtectionTest extends TestCase
{
    use ActsAsAdmin, RefreshDatabase;

    /**
     * @return list<array{0: string, 1: string}> couples [verbe, URL]
     */
    private function protectedWriteEndpoints(): array
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create();
        $post = Post::factory()->create();
        $node = EncyclopediaNode::factory()->create();
        $fragment = FragmentNode::factory()->create();
        $wikilink = Wikilink::factory()->create();
        $other = AdminAllowlist::create([
            'email' => 'autre@arquanzia.test',
            'role' => 'editor',
            'created_by_email' => $this->adminEmail,
        ]);

        $article = EncyclopediaArticle::create(['node_id' => $node->id, 'content_md' => 'x']);
        $image = EncyclopediaGalleryImage::create([
            'article_id' => $article->node_id,
            'media_id' => PostMedia::factory()->create()->id,
            'order_index' => 0,
        ]);

        return [
            ['post', route('admin.posts.store', absolute: false)],
            ['put', route('admin.posts.update', $post, false)],
            ['delete', route('admin.posts.destroy', $post, false)],

            ['post', route('admin.users.store', absolute: false)],
            ['post', route('admin.admins.store', absolute: false)],
            ['delete', route('admin.admins.destroy', $other, false)],

            ['post', route('admin.books.store', absolute: false)],
            ['put', route('admin.books.update', $book, false)],
            ['delete', route('admin.books.destroy', $book, false)],

            ['post', route('admin.chapters.store', $book, false)],
            ['put', route('admin.chapters.update', [$book, $chapter], false)],
            ['delete', route('admin.chapters.destroy', [$book, $chapter], false)],

            ['post', route('admin.encyclopedia.store', absolute: false)],
            ['put', route('admin.encyclopedia.update', $node, false)],
            ['delete', route('admin.encyclopedia.destroy', $node, false)],
            ['delete', route('admin.encyclopedia.gallery.destroy', [$node, $image], false)],
            ['post', route('admin.encyclopedia.import.analyze', absolute: false)],
            ['post', route('admin.encyclopedia.import.execute', absolute: false)],

            ['post', route('admin.fragments.store', absolute: false)],
            ['put', route('admin.fragments.update', $fragment, false)],
            ['delete', route('admin.fragments.destroy', $fragment, false)],

            ['post', route('admin.wikilinks.store', absolute: false)],
            ['put', route('admin.wikilinks.update', $wikilink, false)],
            ['delete', route('admin.wikilinks.destroy', $wikilink, false)],

            ['post', route('admin.settings.logo', absolute: false)],
            ['delete', route('admin.settings.logo.delete', absolute: false)],
            ['post', route('admin.settings.name', absolute: false)],
        ];
    }

    public function test_aucune_ecriture_n_est_accessible_sans_session_admin(): void
    {
        $endpoints = $this->protectedWriteEndpoints();

        $this->assertCount(27, $endpoints, 'Toute nouvelle route d’écriture doit être ajoutée à ce balayage.');

        foreach ($endpoints as [$verb, $url]) {
            $this->{$verb}($url)->assertRedirect(route('admin.login'), "$verb $url devrait être protégé.");
        }
    }

    public function test_aucune_donnee_n_est_modifiee_par_une_tentative_non_authentifiee(): void
    {
        $book = Book::factory()->create(['title' => 'Titre authentique']);
        $post = Post::factory()->create(['title' => 'Billet authentique']);

        $this->put(route('admin.books.update', $book, false), ['title' => 'Pirate']);
        $this->delete(route('admin.books.destroy', $book, false));
        $this->put(route('admin.posts.update', $post, false), ['title' => 'Pirate']);
        $this->delete(route('admin.posts.destroy', $post, false));

        $this->assertSame('Titre authentique', $book->fresh()->title);
        $this->assertSame('Billet authentique', $post->fresh()->title);
        $this->assertNotSoftDeleted('posts', ['id' => $post->id]);
    }
}
