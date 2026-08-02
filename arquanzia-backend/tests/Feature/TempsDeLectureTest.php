<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempsDeLectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_chapitre_annonce_sa_duree(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create(['content_md' => str_repeat('mot ', 600)]);

        $this->get(route('library.chapter', [$book->slug, $chapter->slug], false))
            ->assertSuccessful()
            ->assertSee('3 min', escape: false)
            ->assertSee('600 mots', escape: false);
    }

    public function test_un_livre_additionne_ses_chapitres_publies(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => str_repeat('mot ', 400)]);
        Chapter::factory()->for($book)->create(['content_md' => str_repeat('mot ', 400)]);

        $this->assertSame(800, $book->readingTime()->words);
    }

    /** Un brouillon ne doit pas allonger la durée annoncée d'un livre. */
    public function test_un_chapitre_non_publie_n_est_pas_compte(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => str_repeat('mot ', 400)]);
        Chapter::factory()->for($book)->draft()->create(['content_md' => str_repeat('mot ', 9000)]);

        $this->assertSame(400, $book->readingTime()->words);
    }

    public function test_un_article_d_encyclopedie_annonce_sa_duree(): void
    {
        $node = EncyclopediaNode::factory()->create(['teaser_md' => null]);
        EncyclopediaArticle::create(['node_id' => $node->id, 'content_md' => str_repeat('mot ', 400)]);

        $this->get(route('encyclopedia.show', $node->getFullPath(), false))
            ->assertSuccessful()
            ->assertSee('2 min', escape: false);
    }

    /** Un chapitre vide ne doit pas afficher « 0 min », ni une mention vide. */
    public function test_un_texte_vide_n_affiche_aucune_duree(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create(['content_md' => null]);

        $this->get(route('library.chapter', [$book->slug, $chapter->slug], false))
            ->assertSuccessful()
            ->assertDontSee('0 min', escape: false)
            ->assertDontSee('mots', escape: false);
    }

    public function test_la_liste_de_la_bibliotheque_annonce_les_durees(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => str_repeat('mot ', 1000)]);

        $this->get('/bibliotheque')->assertSuccessful()->assertSee('5 min', escape: false);
    }

    /** Le bandeau promotionnel n'est pas le texte : il ne doit pas allonger la lecture. */
    public function test_le_bandeau_promotionnel_n_est_pas_compte(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create([
            'content_md' => str_repeat('mot ', 400),
            'promo_banner_enabled' => true,
            'promo_banner_text' => str_repeat('promotion ', 500),
        ]);

        $this->assertSame(400, $chapter->readingTime()->words);
    }
}
