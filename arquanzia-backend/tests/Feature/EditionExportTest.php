<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Services\BookExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditionExportTest extends TestCase
{
    use RefreshDatabase;

    private function livre(int $chapitres = 3): Book
    {
        $book = Book::factory()->create(['title' => 'Les Cendres']);

        foreach (range(1, $chapitres) as $i) {
            Chapter::factory()->for($book)->create([
                'title' => "Chapitre {$i}",
                'content_md' => str_repeat('Du texte pour remplir la page. ', 80),
                'order_index' => $i,
            ]);
        }

        return $book;
    }

    public function test_le_format_edition_produit_un_pdf(): void
    {
        $result = app(BookExportService::class)->export($this->livre(), 'edition', []);

        $this->assertSame('application/pdf', $result['mime']);
        $this->assertNotEmpty($result['content']);
        $this->assertStringStartsWith('%PDF', $result['content']);
    }

    /** L'imposition suppose un nombre de pages multiple de quatre : le remplissage doit tenir. */
    public function test_un_livre_tres_court_s_impose_quand_meme(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->create(['content_md' => 'Une seule ligne.']);

        $result = app(BookExportService::class)->export($book, 'edition', []);

        $this->assertStringStartsWith('%PDF', $result['content']);
    }

    public function test_le_telechargement_repond(): void
    {
        $book = $this->livre();

        $this->get(route('download.book', ['slug' => $book->slug, 'format' => 'edition'], false))
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf');
    }

    /**
     * L'imposition existait, la route l'acceptait, et aucune vue ne la proposait — seconde
     * fonctionnalité orpheline du projet après les Chroniques.
     */
    public function test_le_livret_est_propose_sur_la_fiche_du_livre(): void
    {
        $book = $this->livre();

        $this->get(route('library.book', $book->slug, false))
            ->assertSuccessful()
            ->assertSee(route('download.book', ['slug' => $book->slug, 'format' => 'edition']), escape: false)
            ->assertSee('Livret à relier', escape: false);
    }

    public function test_un_livre_sans_chapitre_publie_ne_propose_aucun_telechargement(): void
    {
        $book = Book::factory()->create();

        $this->get(route('library.book', $book->slug, false))
            ->assertSuccessful()
            ->assertDontSee('Livret à relier', escape: false);
    }

    /**
     * Le repli sur la police était testé mais pas utilisé : toute exportation sans options
     * explicites s'interrompait. generateEpub() et generatePdf() en faisaient partie.
     */
    public function test_une_exportation_sans_options_fonctionne(): void
    {
        $book = $this->livre();
        $service = app(BookExportService::class);

        foreach (['pdf', 'epub', 'edition'] as $format) {
            $this->assertNotEmpty($service->export($book, $format)['content'], "Le format {$format} échoue sans options.");
        }
    }

    public function test_une_police_inconnue_retombe_sur_la_standard(): void
    {
        $result = app(BookExportService::class)->export($this->livre(), 'pdf', ['font' => 'inexistante']);

        $this->assertNotEmpty($result['content']);
    }

    public function test_un_livre_non_publie_reste_refuse(): void
    {
        $book = Book::factory()->draft()->create();
        Chapter::factory()->for($book)->create();

        $this->get(route('download.book', ['slug' => $book->slug, 'format' => 'edition'], false))
            ->assertNotFound();
    }
}
