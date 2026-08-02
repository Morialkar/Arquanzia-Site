<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Insère les notes d'autrice à la suite du paragraphe qu'elles commentent.
 *
 * Les notes restent masquées jusqu'à ce que le lecteur les demande : le texte passe d'abord,
 * la note est un bonus assumé. Le repliement s'appuie sur `<details>`, qui fonctionne sans
 * JavaScript — une note inaccessible faute de script serait une promesse non tenue.
 */
class AuthorNoteInjector
{
    /**
     * @param  Collection<int, \App\Models\AuthorNote>  $notes
     */
    public static function inject(string $html, Collection $notes): string
    {
        if ($notes->isEmpty() || trim($html) === '') {
            return $html;
        }

        $parNote = $notes->groupBy('paragraph_id');

        // On agit sur le HTML déjà ancré : chaque paragraphe y porte son identifiant, ce qui
        // évite de recalculer les empreintes et garantit l'accord avec ce qui est affiché.
        return preg_replace_callback(
            '/(<p id="([^"]+)".*?<\/p>)/s',
            function (array $m) use ($parNote): string {
                $notesDuParagraphe = $parNote->get($m[2]);

                if (! $notesDuParagraphe) {
                    return $m[1];
                }

                return $m[1].self::render($notesDuParagraphe);
            },
            $html,
        ) ?? $html;
    }

    /**
     * @param  Collection<int, \App\Models\AuthorNote>  $notes
     */
    private static function render(Collection $notes): string
    {
        $contenu = $notes
            ->map(fn ($note) => '<div class="author-note-corps">'.$note->note_html.'</div>')
            ->implode('');

        return '<details class="author-note">'
            .'<summary class="author-note-titre">Note d’autrice</summary>'
            .$contenu
            .'</details>';
    }
}
