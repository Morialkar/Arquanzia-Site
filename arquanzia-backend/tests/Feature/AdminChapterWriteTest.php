<?php

namespace Tests\Feature;

use App\Models\AdminAllowlist;
use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Premier test d'écriture du back-office (lot 2.5), né d'un bug réel : publier un chapitre —
 * passer de brouillon à publié — plantait sur deux services supprimés à la refonte
 * (ChapterDeliveryService, NotificationService). Le chapitre était publié, puis l'admin
 * recevait une erreur 500. Le flux central du site explosait dans son cas nominal.
 */
class AdminChapterWriteTest extends TestCase
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

    public function test_publier_un_chapitre_en_brouillon_fonctionne(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->draft()->create();

        $this->actingAsAdmin()
            ->put(route('admin.chapters.update', [$book, $chapter], false), [
                'title' => $chapter->title,
                'slug' => $chapter->slug,
                'order_index' => $chapter->order_index,
                'content_md' => $chapter->content_md,
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.books.edit', $book))
            ->assertSessionHas('success');

        $this->assertTrue($chapter->fresh()->is_published);
    }

    public function test_creer_un_chapitre_fonctionne(): void
    {
        $book = Book::factory()->create();

        $this->actingAsAdmin()
            ->post(route('admin.chapters.store', $book, false), [
                'title' => 'Nouveau chapitre',
                'order_index' => 1,
                'content_md' => 'Du contenu.',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.books.edit', $book));

        $this->assertDatabaseHas('chapters', [
            'book_id' => $book->id,
            'title' => 'Nouveau chapitre',
            'is_published' => true,
        ]);
    }

    public function test_depublier_un_chapitre_fonctionne(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->actingAsAdmin()
            ->put(route('admin.chapters.update', [$book, $chapter], false), [
                'title' => $chapter->title,
                'slug' => $chapter->slug,
                'order_index' => $chapter->order_index,
                'content_md' => $chapter->content_md,
                // is_published absent : la case décochée n'envoie rien
            ])
            ->assertRedirect(route('admin.books.edit', $book));

        $this->assertFalse($chapter->fresh()->is_published);
    }

    public function test_une_ecriture_sans_session_admin_est_refusee(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->draft()->create();

        $this->put(route('admin.chapters.update', [$book, $chapter], false), [
            'title' => 'Tentative',
            'order_index' => 0,
            'is_published' => '1',
        ])->assertRedirect(route('admin.login'));

        $this->assertFalse($chapter->fresh()->is_published, 'Rien ne doit être modifié sans session admin.');
    }

    public function test_supprimer_un_chapitre_fonctionne(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->actingAsAdmin()
            ->delete(route('admin.chapters.destroy', [$book, $chapter], false))
            ->assertRedirect(route('admin.books.edit', $book));

        $this->assertDatabaseMissing('chapters', ['id' => $chapter->id]);
    }
}
