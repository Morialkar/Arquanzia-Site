<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le statut de publication est la seule règle d'accès du site : il n'y a pas de compte lecteur
 * et tout le reste est en lecture publique. C'est donc ici que doit se trouver l'essentiel du
 * filet de sécurité.
 */
class PublicationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_livre_non_publie_est_introuvable(): void
    {
        $book = Book::factory()->draft()->create();

        $this->get(route('library.book', $book->slug, false))->assertNotFound();
    }

    public function test_un_livre_non_publie_n_apparait_pas_dans_la_bibliotheque(): void
    {
        $draft = Book::factory()->draft()->create();
        $published = Book::factory()->create();

        $this->get('/bibliotheque')
            ->assertSuccessful()
            ->assertSee($published->title, escape: false)
            ->assertDontSee($draft->title, escape: false);
    }

    public function test_un_livre_non_publie_n_apparait_ni_dans_la_recherche_ni_dans_le_sitemap(): void
    {
        $book = Book::factory()->draft()->create(['title' => 'Grimoire Secret Introuvable']);

        // La page de recherche réaffiche la requête : on vérifie donc l'absence du slug, qui
        // n'apparaît que dans l'URL d'un résultat listé.
        $this->get('/recherche?q=Grimoire+Secret+Introuvable')
            ->assertSuccessful()
            ->assertDontSee($book->slug);

        $this->get('/api/recherche?q=Grimoire+Secret+Introuvable')
            ->assertSuccessful()
            ->assertJsonMissing(['title' => $book->title]);

        $this->get('/sitemap.xml')->assertSuccessful()->assertDontSee($book->slug);
    }

    public function test_un_chapitre_non_publie_est_introuvable(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->draft()->create();

        $this->get(route('library.chapter', [$book->slug, $chapter->slug], false))->assertNotFound();
    }

    public function test_un_chapitre_a_paraitre_est_introuvable(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->comingSoon()->create();

        $this->get(route('library.chapter', [$book->slug, $chapter->slug], false))->assertNotFound();
    }

    public function test_un_chapitre_d_un_livre_non_publie_est_introuvable(): void
    {
        $book = Book::factory()->draft()->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->get(route('library.chapter', [$book->slug, $chapter->slug], false))->assertNotFound();
    }

    public function test_une_entree_d_encyclopedie_en_brouillon_est_introuvable_par_url_directe(): void
    {
        $node = EncyclopediaNode::factory()->draft()->create();

        $this->get(route('encyclopedia.show', $node->slug, false))->assertNotFound();
    }

    /** Un brouillon doit rendre tout son sous-arbre inaccessible, pas seulement lui-même. */
    public function test_une_categorie_en_brouillon_masque_ses_enfants(): void
    {
        $category = EncyclopediaNode::factory()->category()->draft()->create();
        $child = EncyclopediaNode::factory()->create(['parent_id' => $category->id]);

        $this->get(route('encyclopedia.show', $category->slug.'/'.$child->slug, false))
            ->assertNotFound();
    }

    public function test_un_brouillon_d_encyclopedie_est_absent_des_surfaces_de_decouverte(): void
    {
        $draft = EncyclopediaNode::factory()->draft()->create(['title' => 'Savoir Interdit Brouillon']);

        $this->get('/encyclopedie')->assertSuccessful()->assertDontSee($draft->title, escape: false);
        // Même précaution que ci-dessus : la requête est réaffichée, on cible le slug.
        $this->get('/recherche?q=Savoir+Interdit+Brouillon')->assertSuccessful()->assertDontSee($draft->slug);
        $this->get('/sitemap.xml')->assertSuccessful()->assertDontSee($draft->slug);
        $this->get('/')->assertSuccessful()->assertDontSee($draft->title, escape: false);
    }

    public function test_le_telechargement_d_un_livre_non_publie_est_refuse(): void
    {
        $book = Book::factory()->draft()->create();

        $this->get(route('download.book', ['slug' => $book->slug, 'format' => 'pdf'], false))
            ->assertNotFound();
    }

    public function test_le_telechargement_d_un_chapitre_non_publie_est_refuse(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->draft()->create();

        $this->get(route('download.chapter', [
            'book' => $book->slug,
            'chapter' => $chapter->slug,
            'format' => 'pdf',
        ], false))->assertNotFound();
    }
}
