<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaNode;
use App\Support\ParagraphAnchors;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ActsAsAdmin;
use Tests\TestCase;

class NotesAutriceTest extends TestCase
{
    use ActsAsAdmin, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAdmin();
    }

    private function chapitre(string $contenu = "Le premier passage.\n\nLe second passage."): Chapter
    {
        $book = Book::factory()->create();

        return Chapter::factory()->for($book)->create(['content_md' => $contenu]);
    }

    private function lire(Chapter $chapter): \Illuminate\Testing\TestResponse
    {
        return $this->get(route('library.chapter', [$chapter->book->slug, $chapter->slug], false));
    }

    // — Affichage —

    public function test_une_note_s_affiche_au_bon_paragraphe(): void
    {
        $chapter = $this->chapitre();
        $chapter->authorNotes()->create([
            'paragraph_id' => ParagraphAnchors::identifierFor('Le second passage.'),
            'note_md' => 'Ici j’hésitais entre deux versions.',
        ]);

        $html = $this->lire($chapter)->assertSuccessful()->getContent();

        $this->assertStringContainsString('Ici j’hésitais entre deux versions.', $html);

        // La note suit son paragraphe, pas l'autre.
        $posSecond = strpos($html, ParagraphAnchors::identifierFor('Le second passage.'));
        $posNote = strpos($html, 'hésitais');
        $this->assertGreaterThan($posSecond, $posNote);
    }

    /** Le texte passe d'abord : la note est repliée tant qu'on ne la demande pas. */
    public function test_une_note_est_repliee_par_defaut(): void
    {
        $chapter = $this->chapitre();
        $chapter->authorNotes()->create([
            'paragraph_id' => ParagraphAnchors::identifierFor('Le premier passage.'),
            'note_md' => 'Un commentaire.',
        ]);

        $html = $this->lire($chapter)->getContent();

        $this->assertStringContainsString('<details class="author-note">', $html);
        $this->assertStringNotContainsString('<details class="author-note" open', $html);
    }

    public function test_un_texte_sans_note_n_affiche_rien(): void
    {
        $this->lire($this->chapitre())->assertDontSee('author-note', escape: false);
    }

    public function test_le_markdown_d_une_note_est_rendu(): void
    {
        $chapter = $this->chapitre();
        $chapter->authorNotes()->create([
            'paragraph_id' => ParagraphAnchors::identifierFor('Le premier passage.'),
            'note_md' => 'Un mot **important**.',
        ]);

        $this->lire($chapter)->assertSee('<strong>important</strong>', escape: false);
    }

    public function test_une_note_s_affiche_aussi_sur_un_article(): void
    {
        $node = EncyclopediaNode::factory()->create();
        EncyclopediaArticle::create(['node_id' => $node->id, 'content_md' => 'Un paragraphe d’encyclopédie.']);
        $node->authorNotes()->create([
            'paragraph_id' => ParagraphAnchors::identifierFor('Un paragraphe d’encyclopédie.'),
            'note_md' => 'Note sur cette entrée.',
        ]);

        $this->get(route('encyclopedia.show', $node->getFullPath(), false))
            ->assertSuccessful()
            ->assertSee('Note sur cette entrée.', escape: false);
    }

    // — Étanchéité —

    /** Une note qui remonterait dans un résultat de recherche désorienterait, sans contexte. */
    public function test_une_note_n_apparait_ni_dans_le_flux_ni_dans_la_recherche(): void
    {
        $chapter = $this->chapitre();
        $chapter->authorNotes()->create([
            'paragraph_id' => ParagraphAnchors::identifierFor('Le premier passage.'),
            'note_md' => 'Confidence rarissime.',
        ]);

        $this->get(route('feeds.atom', [], false))->assertDontSee('Confidence rarissime.', escape: false);
        $this->get('/recherche?q=rarissime')->assertDontSee('Confidence rarissime.', escape: false);
    }

    public function test_une_note_n_allonge_pas_la_duree_de_lecture(): void
    {
        $chapter = $this->chapitre(str_repeat('mot ', 400));
        $avant = $chapter->readingTime()->words;

        $chapter->authorNotes()->create([
            'paragraph_id' => 'p-abcdef12',
            'note_md' => str_repeat('note ', 500),
        ]);

        $this->assertSame($avant, $chapter->fresh()->readingTime()->words);
    }

    public function test_supprimer_un_texte_supprime_ses_notes(): void
    {
        $chapter = $this->chapitre();
        $chapter->authorNotes()->create(['paragraph_id' => 'p-abcdef12', 'note_md' => 'Une note.']);

        $chapter->delete();

        $this->assertDatabaseCount('author_notes', 0);
    }

    // — Administration —

    public function test_l_ecran_liste_les_paragraphes_annotables(): void
    {
        $chapter = $this->chapitre();

        $this->actingAsAdmin()
            ->get(route('admin.notes.edit', ['chapitre', $chapter->id], false))
            ->assertSuccessful()
            ->assertSee('Le premier passage.', escape: false)
            ->assertSee('Le second passage.', escape: false);
    }

    public function test_une_note_peut_etre_creee_puis_modifiee(): void
    {
        $chapter = $this->chapitre();
        $ancre = ParagraphAnchors::identifierFor('Le premier passage.');
        $url = route('admin.notes.store', ['chapitre', $chapter->id], false);

        $this->actingAsAdmin()->post($url, ['paragraph_id' => $ancre, 'note_md' => 'Première version.']);
        $this->assertDatabaseHas('author_notes', ['paragraph_id' => $ancre, 'note_md' => 'Première version.']);

        $this->actingAsAdmin()->post($url, ['paragraph_id' => $ancre, 'note_md' => 'Version révisée.']);
        $this->assertDatabaseCount('author_notes', 1);
        $this->assertDatabaseHas('author_notes', ['note_md' => 'Version révisée.']);
    }

    /** Vider le champ vaut suppression : c'est le geste attendu. */
    public function test_vider_le_champ_supprime_la_note(): void
    {
        $chapter = $this->chapitre();
        $ancre = ParagraphAnchors::identifierFor('Le premier passage.');
        $chapter->authorNotes()->create(['paragraph_id' => $ancre, 'note_md' => 'À retirer.']);

        $this->actingAsAdmin()->post(
            route('admin.notes.store', ['chapitre', $chapter->id], false),
            ['paragraph_id' => $ancre, 'note_md' => ''],
        );

        $this->assertDatabaseCount('author_notes', 0);
    }

    // — Notes détachées —

    /**
     * Le cœur de la décision : réécrire un paragraphe change son identifiant. La note est
     * conservée et signalée, jamais rattachée au jugé.
     */
    public function test_reecrire_un_paragraphe_detache_la_note_sans_la_perdre(): void
    {
        $chapter = $this->chapitre();
        $chapter->authorNotes()->create([
            'paragraph_id' => ParagraphAnchors::identifierFor('Le premier passage.'),
            'note_md' => 'Une confidence à ne pas perdre.',
        ]);

        $chapter->update(['content_md' => "Le premier passage, entièrement réécrit.\n\nLe second passage."]);

        // La note existe toujours…
        $this->assertDatabaseCount('author_notes', 1);

        // …ne s'affiche plus au lecteur…
        $this->lire($chapter)->assertDontSee('Une confidence à ne pas perdre.', escape: false);

        // …et l'administration la signale.
        $this->actingAsAdmin()
            ->get(route('admin.notes.edit', ['chapitre', $chapter->id], false))
            ->assertSuccessful()
            ->assertSee('détachée', escape: false)
            ->assertSee('Une confidence à ne pas perdre.', escape: false);
    }

    public function test_une_note_detachee_peut_etre_supprimee(): void
    {
        $chapter = $this->chapitre();
        $note = $chapter->authorNotes()->create(['paragraph_id' => 'p-obsolete', 'note_md' => 'Périmée.']);

        $this->actingAsAdmin()->delete(
            route('admin.notes.destroy', ['chapitre', $chapter->id, $note], false)
        );

        $this->assertDatabaseCount('author_notes', 0);
    }

    /** Une note d'un autre texte ne doit pas pouvoir être supprimée par cette voie. */
    public function test_une_note_d_un_autre_texte_ne_peut_pas_etre_supprimee(): void
    {
        $chapter = $this->chapitre();
        $autre = $this->chapitre();
        $note = $autre->authorNotes()->create(['paragraph_id' => 'p-abcdef12', 'note_md' => 'Ailleurs.']);

        $this->actingAsAdmin()
            ->delete(route('admin.notes.destroy', ['chapitre', $chapter->id, $note], false))
            ->assertNotFound();

        $this->assertDatabaseCount('author_notes', 1);
    }

    public function test_un_type_inconnu_est_refuse(): void
    {
        $this->actingAsAdmin()
            ->get(route('admin.notes.edit', ['imprimante', '00000000-0000-0000-0000-000000000000'], false))
            ->assertNotFound();
    }

    public function test_l_ecran_est_inaccessible_sans_session(): void
    {
        $chapter = $this->chapitre();

        $this->get(route('admin.notes.edit', ['chapitre', $chapter->id], false))
            ->assertRedirect(route('admin.login'));
    }
}
