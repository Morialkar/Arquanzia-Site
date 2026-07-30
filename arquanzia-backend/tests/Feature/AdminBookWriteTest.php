<?php

namespace Tests\Feature;

use App\Models\AdminAllowlist;
use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookWriteTest extends TestCase
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

    public function test_creer_un_livre_fonctionne(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.books.store', absolute: false), [
                'title' => 'Les Cendres du Pacte',
                'author' => 'Naomi',
                'description_md' => 'Un récit.',
            ])
            ->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseHas('books', [
            'title' => 'Les Cendres du Pacte',
            'slug' => 'les-cendres-du-pacte',
            'is_published' => false,
        ]);
    }

    public function test_modifier_un_livre_fonctionne(): void
    {
        $book = Book::factory()->draft()->create();

        $this->actingAsAdmin()
            ->put(route('admin.books.update', $book, false), [
                'title' => 'Titre révisé',
                'author' => 'Naomi',
                'description_md' => 'Description révisée.',
            ])
            ->assertRedirect(route('admin.books.edit', $book));

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Titre révisé']);
    }

    public function test_supprimer_un_livre_emporte_ses_chapitres(): void
    {
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create();

        $this->actingAsAdmin()
            ->delete(route('admin.books.destroy', $book, false))
            ->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('chapters', ['id' => $chapter->id]);
    }

    public function test_une_ecriture_sans_session_admin_est_refusee(): void
    {
        $book = Book::factory()->draft()->create();

        $this->put(route('admin.books.update', $book, false), ['title' => 'Pirate'])
            ->assertRedirect(route('admin.login'));

        $this->assertSame($book->title, $book->fresh()->title);
    }

    // — Gel du slug après publication —

    public function test_le_slug_d_un_brouillon_reste_modifiable(): void
    {
        $book = Book::factory()->draft()->create(['slug' => 'ancien-slug']);

        $this->actingAsAdmin()
            ->put(route('admin.books.update', $book, false), [
                'title' => $book->title,
                'slug' => 'nouveau-slug',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('nouveau-slug', $book->fresh()->slug);
    }

    public function test_publier_un_livre_gele_son_slug(): void
    {
        $book = Book::factory()->draft()->create();
        $this->assertNull($book->slug_locked_at);

        $this->actingAsAdmin()->put(route('admin.books.update', $book, false), [
            'title' => $book->title,
            'slug' => $book->slug,
            'is_published' => '1',
        ]);

        $this->assertNotNull($book->fresh()->slug_locked_at);
    }

    public function test_creer_un_livre_deja_publie_gele_son_slug(): void
    {
        $this->actingAsAdmin()->post(route('admin.books.store', absolute: false), [
            'title' => 'Publié tout de suite',
            'is_published' => '1',
        ]);

        $this->assertNotNull(Book::where('title', 'Publié tout de suite')->first()->slug_locked_at);
    }

    public function test_le_slug_d_un_livre_publie_ne_peut_plus_changer(): void
    {
        $book = Book::factory()->create(['slug' => 'slug-fige']);

        $this->actingAsAdmin()
            ->put(route('admin.books.update', $book, false), [
                'title' => $book->title,
                'slug' => 'slug-tout-neuf',
                'is_published' => '1',
            ])
            ->assertSessionHasErrors('slug');

        $this->assertSame('slug-fige', $book->fresh()->slug);
    }

    /**
     * Le repli `Str::slug($title)` du contrôleur régénérerait le slug depuis le titre si le
     * champ n'était pas soumis — le champ étant en lecture seule, c'est le cas nominal.
     */
    public function test_renommer_le_titre_ne_regenere_pas_le_slug_d_un_livre_publie(): void
    {
        $book = Book::factory()->create(['slug' => 'slug-fige', 'title' => 'Titre initial']);

        $this->actingAsAdmin()
            ->put(route('admin.books.update', $book, false), [
                'title' => 'Titre complètement différent',
                'is_published' => '1',
            ])
            ->assertSessionHasNoErrors();

        $fresh = $book->fresh();
        $this->assertSame('slug-fige', $fresh->slug);
        $this->assertSame('Titre complètement différent', $fresh->title);
    }

    /** Dépublier ne doit pas rouvrir le verrou, sinon la règle se contourne en trois étapes. */
    public function test_depublier_puis_renommer_ne_contourne_pas_le_gel(): void
    {
        $book = Book::factory()->create(['slug' => 'slug-fige']);

        $this->actingAsAdmin()->put(route('admin.books.update', $book, false), [
            'title' => $book->title,
            'slug' => $book->slug,
        ]);

        $this->assertFalse($book->fresh()->is_published);
        $this->assertNotNull($book->fresh()->slug_locked_at, 'Le verrou doit survivre à une dépublication.');

        $this->actingAsAdmin()
            ->put(route('admin.books.update', $book, false), [
                'title' => $book->title,
                'slug' => 'slug-contourne',
            ])
            ->assertSessionHasErrors('slug');

        $this->assertSame('slug-fige', $book->fresh()->slug);
    }
}
