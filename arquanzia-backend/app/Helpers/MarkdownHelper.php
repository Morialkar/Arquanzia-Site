<?php

namespace App\Helpers;

use App\Models\Wikilink;
use Illuminate\Support\Str;

/**
 * Rendu du Markdown rédigé dans Obsidian.
 *
 * Le rendu lui-même revient à CommonMark, via Str::markdown(). Ce qui reste ici est un
 * pré-traitement des habitudes d'Obsidian, qui produit du balisage que CommonMark refuse
 * d'interpréter : emphases laissées ouvertes en fin de ligne, lignes ne contenant que des
 * astérisques, et liens internes en [[double crochet]].
 *
 * Les sauts de ligne simples sont rendus par l'option soft_break de CommonMark, et non par un
 * nl2br() appliqué au HTML produit : celui-ci insérait un <br /> après chaque balise de bloc,
 * ajoutant un saut fantôme après chaque paragraphe, chaque liste et chaque élément de liste.
 */
class MarkdownHelper
{
    /** Les auteurs écrivent au fil du texte : un retour à la ligne reste un retour à la ligne. */
    private const OPTIONS = [
        'renderer' => ['soft_break' => "<br />\n"],
        // Le contenu est rédigé par l'administration et contient du HTML délibéré — images
        // dimensionnées, mises en page de galerie. Aucune saisie de visiteur ne passe ici.
        'html_input' => 'allow',
        'allow_unsafe_links' => false,
    ];

    public static function render(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        $text = self::normalizeLineEndings($markdown);
        $text = self::closeDanglingEmphasis($text);
        $text = self::renderWikilinks($text);

        return Str::markdown($text, self::OPTIONS);
    }

    private static function normalizeLineEndings(string $text): string
    {
        return str_replace(["\r\n", "\r"], "\n", $text);
    }

    /**
     * Referme les emphases laissées ouvertes et retire les astérisques orphelines.
     *
     * Obsidian tolère `**du gras` sans fermeture, et laisse traîner des lignes composées
     * d'astérisques seules quand on supprime un passage. CommonMark, lui, affiche les
     * astérisques telles quelles — le texte se retrouve constellé d'étoiles.
     */
    private static function closeDanglingEmphasis(string $text): string
    {
        $lines = explode("\n", $text);
        $result = [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if ($trimmed === '') {
                $result[] = '';

                continue;
            }

            // Vestige d'une suppression de passage : la ligne n'a plus de contenu.
            if ($trimmed === '*' || $trimmed === '**') {
                continue;
            }

            // Une puce commence par une astérisque sans être une emphase.
            if (preg_match('/^[*\-]\s+/', $trimmed)) {
                $result[] = $line;

                continue;
            }

            if (substr_count($line, '**') % 2 !== 0) {
                $line .= '**';
            }

            if (substr_count(str_replace('**', '', $line), '*') % 2 !== 0) {
                $closing = self::findItalicClosingLine($lines, $i, $count);

                if ($closing > $i) {
                    // L'italique court sur plusieurs lignes : on referme sur la dernière.
                    $result[] = $line;
                    for ($k = $i + 1; $k <= $closing; $k++) {
                        $result[] = $k === $closing ? $lines[$k].'*' : $lines[$k];
                    }
                    $i = $closing;

                    continue;
                }

                $line .= '*';
            }

            $result[] = $line;
        }

        return implode("\n", $result);
    }

    /** Dernière ligne du bloc courant, sur laquelle refermer un italique resté ouvert. */
    private static function findItalicClosingLine(array $lines, int $start, int $count): int
    {
        $j = $start + 1;

        while ($j < $count) {
            $next = trim($lines[$j]);

            if ($next === '' || $next === '*' || $next === '**' || str_starts_with($next, '*')) {
                break;
            }

            $j++;
        }

        return $j - 1;
    }

    /**
     * Convertit `[[Terme]]` et `[[Terme|libellé]]` en liens, quand la cible est connue.
     *
     * Un terme sans correspondance devient du texte simple plutôt qu'un lien mort : mieux vaut
     * une mention muette qu'un lien promettant une page inexistante.
     */
    private static function renderWikilinks(string $text): string
    {
        return preg_replace_callback(
            '/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/',
            function (array $matches): string {
                $term = trim($matches[1]);
                $label = isset($matches[2]) ? trim($matches[2]) : $term;

                $target = Wikilink::resolveTarget($term);

                if (! $target || ! isset($target['url'])) {
                    return e($label);
                }

                $attributes = ' data-wikilink-term="'.e($target['title'] ?? $label).'"';

                if (! empty($target['teaser'])) {
                    $attributes .= ' data-wikilink-teaser="'.e(strip_tags($target['teaser'])).'"';
                }

                return sprintf(
                    '<a href="%s" class="wikilink-resolved"%s>%s</a>',
                    e($target['url']),
                    $attributes,
                    e($label),
                );
            },
            $text,
        ) ?? $text;
    }
}
