<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppressionEnCascadeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * La base efface les chapitres d'un livre en cascade, sans déclencher d'événement de
     * modèle : leurs mentions et leurs notes restaient alors rattachées à des textes disparus.
     */
    public function test_supprimer_un_livre_n_laisse_pas_d_orphelins(): void
    {
        $cible = EncyclopediaNode::factory()->create(['title' => 'Thalria']);
        $book = Book::factory()->create();
        $chapter = Chapter::factory()->for($book)->create(['content_md' => 'Voir [[Thalria]].']);
        $chapter->authorNotes()->create(['paragraph_id' => 'p-abcdef12', 'note_md' => 'Une note.']);

        $this->assertDatabaseCount('mentions', 1);
        $this->assertDatabaseCount('author_notes', 1);

        $book->delete();

        $this->assertDatabaseCount('chapters', 0);
        $this->assertDatabaseCount('mentions', 0);
        $this->assertDatabaseCount('author_notes', 0);
    }

    public function test_supprimer_un_livre_efface_bien_ses_chapitres(): void
    {
        $book = Book::factory()->create();
        Chapter::factory()->for($book)->count(3)->create();

        $book->delete();

        $this->assertDatabaseCount('chapters', 0);
    }

    public function test_supprimer_une_categorie_efface_toute_sa_descendance(): void
    {
        $racine = EncyclopediaNode::factory()->category()->create();
        $intermediaire = EncyclopediaNode::factory()->category()->create(['parent_id' => $racine->id]);
        EncyclopediaNode::factory()->count(2)->create(['parent_id' => $intermediaire->id]);

        $racine->delete();

        $this->assertDatabaseCount('encyclopedia_nodes', 0);
    }

    public function test_supprimer_une_categorie_ne_laisse_pas_d_orphelins(): void
    {
        $cible = EncyclopediaNode::factory()->create(['title' => 'Thalria']);
        $categorie = EncyclopediaNode::factory()->category()->create();
        $enfant = EncyclopediaNode::factory()->create([
            'parent_id' => $categorie->id,
            'teaser_md' => 'Voir [[Thalria]].',
        ]);
        $enfant->authorNotes()->create(['paragraph_id' => 'p-abcdef12', 'note_md' => 'Note.']);

        $this->assertDatabaseCount('mentions', 1);

        $categorie->delete();

        $this->assertDatabaseCount('mentions', 0);
        $this->assertDatabaseCount('author_notes', 0);
    }
}
