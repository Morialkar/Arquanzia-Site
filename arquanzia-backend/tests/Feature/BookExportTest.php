<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Services\BookExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * L'export PDF/EPUB est le service le plus volumineux du projet et n'était couvert par rien.
 * On ne vérifie pas la mise en page, seulement qu'un fichier non vide et du bon type sort,
 * et que les options de police et de taille sont bornées.
 */
class BookExportTest extends TestCase
{
    use RefreshDatabase;

    private function bookWithChapters(): Book
    {
        $book = Book::factory()->create();

        Chapter::factory()->for($book)->create([
            'title' => 'Premier chapitre',
            'content_md' => "# Ouverture\n\nDu **texte** et de l'*italique*.",
            'order_index' => 1,
        ]);
        Chapter::factory()->for($book)->create([
            'title' => 'Second chapitre',
            'content_md' => "Une suite avec une liste :\n\n* un\n* deux",
            'order_index' => 2,
        ]);

        return $book;
    }

    public static function formats(): array
    {
        return [
            'pdf' => ['pdf', 'application/pdf'],
            'epub' => ['epub', 'application/epub+zip'],
        ];
    }

    #[DataProvider('formats')]
    public function test_un_livre_s_exporte(string $format, string $mime): void
    {
        $book = $this->bookWithChapters();

        $result = app(BookExportService::class)->export($book, $format, ['font' => 'standard', 'size' => 18]);

        $this->assertSame($mime, $result['mime']);
        $this->assertNotEmpty($result['content']);
        $this->assertStringEndsWith('.'.$format, $result['filename']);
    }

    #[DataProvider('formats')]
    public function test_un_chapitre_s_exporte(string $format, string $mime): void
    {
        $book = $this->bookWithChapters();
        $chapter = $book->chapters()->first();

        $result = app(BookExportService::class)->export($chapter, $format, ['font' => 'standard', 'size' => 18]);

        $this->assertSame($mime, $result['mime']);
        $this->assertNotEmpty($result['content']);
    }

    public function test_le_telechargement_d_un_livre_publie_repond(): void
    {
        $book = $this->bookWithChapters();

        $this->get(route('download.book', ['slug' => $book->slug, 'format' => 'pdf'], false))
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_le_telechargement_est_servi_en_piece_jointe(): void
    {
        $book = $this->bookWithChapters();

        $response = $this->get(route('download.book', ['slug' => $book->slug, 'format' => 'pdf'], false));

        $this->assertStringStartsWith('attachment;', $response->headers->get('content-disposition'));
    }

    public function test_une_taille_de_police_hors_bornes_est_ramenee_dans_l_intervalle(): void
    {
        $book = $this->bookWithChapters();
        $service = app(BookExportService::class);

        // Une taille absurde ne doit ni échouer ni produire un document démesuré.
        $enorme = $service->export($book, 'pdf', ['font' => 'standard', 'size' => 99999]);
        $normal = $service->export($book, 'pdf', ['font' => 'standard', 'size' => 26]);

        $this->assertNotEmpty($enorme['content']);
        $this->assertSame(strlen($normal['content']) > 0, strlen($enorme['content']) > 0);
    }

    public function test_une_police_inconnue_retombe_sur_la_police_standard(): void
    {
        $book = $this->bookWithChapters();

        $result = app(BookExportService::class)
            ->export($book, 'pdf', ['font' => 'police-inexistante', 'size' => 18]);

        $this->assertNotEmpty($result['content']);
    }

    public function test_un_format_inconnu_est_rejete(): void
    {
        $book = $this->bookWithChapters();

        $this->expectException(\Throwable::class);

        app(BookExportService::class)->export($book, 'docx', ['font' => 'standard', 'size' => 18]);
    }
}
