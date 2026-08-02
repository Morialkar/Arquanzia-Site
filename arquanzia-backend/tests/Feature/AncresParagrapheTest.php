<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaNode;
use App\Support\ParagraphAnchors;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AncresParagrapheTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_chapitre_ancre_ses_paragraphes(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create([
            'content_md' => "Premier paragraphe.\n\nSecond paragraphe.",
        ]);

        $this->get(route('library.chapter', [$book->slug, $chapter->slug], false))
            ->assertSuccessful()
            ->assertSee('id="'.ParagraphAnchors::identifierFor('Premier paragraphe.').'"', escape: false)
            ->assertSee('class="paragraph-anchor"', escape: false);
    }

    public function test_un_article_d_encyclopedie_ancre_ses_paragraphes(): void
    {
        $node = EncyclopediaNode::factory()->create();
        EncyclopediaArticle::create(['node_id' => $node->id, 'content_md' => 'Un paragraphe citable.']);

        $this->get(route('encyclopedia.show', $node->getFullPath(), false))
            ->assertSuccessful()
            ->assertSee('id="'.ParagraphAnchors::identifierFor('Un paragraphe citable.').'"', escape: false);
    }

    /** Une adresse partagée doit rester valable après modification du reste du texte. */
    public function test_une_adresse_partagee_survit_a_l_ajout_d_un_paragraphe(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create(['content_md' => 'Le passage cité.']);

        $ancre = ParagraphAnchors::identifierFor('Le passage cité.');
        $url = route('library.chapter', [$book->slug, $chapter->slug], false);

        $this->get($url)->assertSee('id="'.$ancre.'"', escape: false);

        $chapter->update(['content_md' => "Un ajout en tête.\n\nLe passage cité."]);

        $this->get($url)->assertSee('id="'.$ancre.'"', escape: false);
    }

    /** Les ancres alourdiraient le contenu envoyé aux agrégateurs sans y servir à rien. */
    public function test_le_flux_ne_contient_pas_les_ancres(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => 'Un paragraphe.']);

        $this->get(route('feeds.atom', [], false))
            ->assertSuccessful()
            ->assertDontSee('paragraph-anchor', escape: false);
    }
}
