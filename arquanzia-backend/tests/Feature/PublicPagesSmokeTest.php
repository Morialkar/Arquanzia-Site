<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rend chaque page publique avec du contenu réel en base.
 *
 * Ce test existe parce que deux pages d'administration ont été livrées cassées par des accès
 * à des attributs supprimés : ni la vérification des noms de routes, ni la compilation des
 * gabarits Blade ne peuvent détecter ce genre de défaut. Seul le rendu effectif le fait.
 */
class PublicPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function seedContent(): array
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create();

        $category = EncyclopediaNode::factory()->category()->create();
        $article = EncyclopediaNode::factory()->create(['parent_id' => $category->id]);

        $fragmentCategory = FragmentNode::factory()->category()->create();

        Post::factory()->create();

        return compact('book', 'chapter', 'category', 'article', 'fragmentCategory');
    }

    public function test_les_pages_publiques_repondent(): void
    {
        $c = $this->seedContent();

        $urls = [
            '/',
            '/fil',
            '/bibliotheque',
            '/encyclopedie',
            '/fragments',
            '/recherche',
            '/recherche?q=' . urlencode($c['book']->title),
            '/api/recherche?q=' . urlencode($c['book']->title),
            '/sitemap.xml',
            '/healthz',
            route('library.chapter', [$c['book']->slug, $c['chapter']->slug], false),
            route('encyclopedia.show', $c['category']->slug, false),
            route('encyclopedia.show', $c['category']->slug . '/' . $c['article']->slug, false),
            route('fragments.show', $c['fragmentCategory']->slug, false),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertSuccessful();
        }
    }

    public function test_la_fiche_d_un_billet_repond(): void
    {
        $post = Post::factory()->create();

        $this->get(route('post.show', $post, false))->assertSuccessful();
    }

    public function test_un_livre_a_chapitre_unique_redirige_vers_ce_chapitre(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->get(route('library.book', $book->slug, false))
            ->assertRedirect(route('library.chapter', [$book->slug, $chapter->slug]));
    }

    public function test_la_page_d_un_livre_a_plusieurs_chapitres_repond(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->count(2)->create();

        $this->get(route('library.book', $book->slug, false))->assertSuccessful();
    }

    public function test_une_page_inexistante_renvoie_404(): void
    {
        $this->get('/cette-page-nexiste-pas')->assertNotFound();
    }
}
