<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MarkdownHelper;
use App\Http\Controllers\Controller;
use App\Models\AuthorNote;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Support\ParagraphAnchors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorNoteController extends Controller
{
    /** Types annotables, exposés par un mot lisible plutôt que par un nom de classe. */
    private const TYPES = [
        'chapitre' => Chapter::class,
        'encyclopedie' => EncyclopediaNode::class,
    ];

    public function edit(string $type, string $id): View
    {
        $notable = $this->resolve($type, $id);

        $paragraphes = ParagraphAnchors::paragraphsIn($this->renderedContent($notable));
        $notes = $notable->authorNotes()->get()->keyBy('paragraph_id');

        // Une note dont le paragraphe a été réécrit ne correspond plus à rien. Elle est
        // conservée et signalée : seule l'autrice sait où elle devait aller, et un
        // rattachement automatique la placerait au mauvais endroit sans prévenir.
        $detachees = $notes->reject(fn ($note, $ancre) => isset($paragraphes[$ancre]));

        return view('admin.notes.edit', [
            'type' => $type,
            'notable' => $notable,
            'titre' => $this->titre($notable),
            'paragraphes' => $paragraphes,
            'notes' => $notes,
            'detachees' => $detachees,
        ]);
    }

    public function store(Request $request, string $type, string $id): RedirectResponse
    {
        $notable = $this->resolve($type, $id);

        $validated = $request->validate([
            'paragraph_id' => 'required|string|max:32',
            'note_md' => 'nullable|string|max:5000',
        ]);

        $existante = $notable->authorNotes()
            ->where('paragraph_id', $validated['paragraph_id'])
            ->first();

        // Vider le champ vaut suppression : c'est le geste attendu, et cela évite un second
        // bouton pour chaque paragraphe.
        if (blank($validated['note_md'])) {
            $existante?->delete();

            return back()->with('success', 'Note supprimée');
        }

        if ($existante) {
            $existante->update(['note_md' => $validated['note_md']]);
        } else {
            $notable->authorNotes()->create([
                'paragraph_id' => $validated['paragraph_id'],
                'note_md' => $validated['note_md'],
            ]);
        }

        return back()->with('success', 'Note enregistrée');
    }

    public function destroy(string $type, string $id, AuthorNote $note): RedirectResponse
    {
        $notable = $this->resolve($type, $id);

        abort_unless(
            $note->notable_id === $notable->getKey() && $note->notable_type === $notable->getMorphClass(),
            404,
        );

        $note->delete();

        return back()->with('success', 'Note supprimée');
    }

    private function resolve(string $type, string $id): Model
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type]::findOrFail($id);
    }

    /** Le HTML rendu, seul endroit où les identifiants de paragraphe sont calculables. */
    private function renderedContent(Model $notable): string
    {
        if ($notable instanceof Chapter) {
            return MarkdownHelper::render($notable->content_md);
        }

        return MarkdownHelper::render($notable->article?->content_md);
    }

    private function titre(Model $notable): string
    {
        return $notable instanceof Chapter
            ? $notable->book->title.' — '.$notable->title
            : $notable->title;
    }
}
