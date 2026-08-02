<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Pose un identifiant sur chaque paragraphe d'un texte rendu, pour qu'un passage précis puisse
 * être cité par son adresse.
 *
 * **L'identifiant dérive du contenu, jamais de la position.** Un numéro d'ordre casserait tous
 * les liens partagés dès qu'un paragraphe est inséré plus haut — le même défaut que le gel des
 * slugs évite ailleurs. Ici, insérer un paragraphe en tête laisse les autres intacts.
 *
 * Le balisage est retiré avant l'empreinte : mettre un mot en gras ne doit pas détacher les
 * liens qui pointaient vers ce paragraphe.
 */
class ParagraphAnchors
{
    private const PREFIX = 'p-';

    /** Longueur de l'empreinte : assez courte pour une adresse, assez longue pour ne pas collider. */
    private const HASH_LENGTH = 8;

    public static function apply(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = self::parse($html);

        if (! $dom) {
            return $html;
        }

        $vus = [];

        foreach (self::topLevelParagraphs($dom) as $paragraphe) {
            $id = self::identifier($paragraphe, $vus);

            if ($id === null) {
                continue;
            }

            $paragraphe->setAttribute('id', $id);
            $paragraphe->appendChild(self::anchorLink($dom, $id));
        }

        return self::serialize($dom);
    }

    /** Identifiant d'un paragraphe, sans le poser — utile pour ancrer une note à ce paragraphe. */
    public static function identifierFor(string $text): string
    {
        return self::PREFIX.substr(sha1(self::normalize($text)), 0, self::HASH_LENGTH);
    }

    private static function parse(string $html): ?DOMDocument
    {
        $previous = libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        // L'encodage doit être déclaré, sans quoi DOMDocument interprète l'UTF-8 comme du
        // latin-1 et mutile les accents.
        $ok = $dom->loadHTML(
            '<?xml encoding="UTF-8"?><div id="arq-racine">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $ok ? $dom : null;
    }

    /**
     * Seuls les paragraphes de premier niveau sont ancrés.
     *
     * Un paragraphe imbriqué dans une citation ou un élément de liste appartient à un ensemble
     * plus large : l'ancrer isolément citerait une phrase hors de son cadre.
     *
     * @return list<DOMElement>
     */
    private static function topLevelParagraphs(DOMDocument $dom): array
    {
        $xpath = new DOMXPath($dom);
        $paragraphes = [];

        foreach ($xpath->query('/div[@id="arq-racine"]/p') ?: [] as $node) {
            if ($node instanceof DOMElement) {
                $paragraphes[] = $node;
            }
        }

        return $paragraphes;
    }

    /**
     * @param  array<string, int>  $vus  empreintes déjà rencontrées, par référence
     */
    private static function identifier(DOMElement $paragraphe, array &$vus): ?string
    {
        $texte = self::normalize($paragraphe->textContent);

        // Un paragraphe sans texte — une image seule, par exemple — n'a rien à citer.
        if ($texte === '') {
            return null;
        }

        $base = self::PREFIX.substr(sha1($texte), 0, self::HASH_LENGTH);

        // Deux paragraphes identiques dans un même texte produiraient la même empreinte.
        $vus[$base] = ($vus[$base] ?? 0) + 1;

        return $vus[$base] === 1 ? $base : $base.'-'.$vus[$base];
    }

    private static function normalize(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');
    }

    private static function anchorLink(DOMDocument $dom, string $id): DOMElement
    {
        $lien = $dom->createElement('a', '¶');
        $lien->setAttribute('href', '#'.$id);
        $lien->setAttribute('class', 'paragraph-anchor');
        $lien->setAttribute('aria-label', 'Lien vers ce paragraphe');
        $lien->setAttribute('data-anchor', $id);

        return $lien;
    }

    private static function serialize(DOMDocument $dom): string
    {
        $racine = $dom->getElementById('arq-racine')
            ?? $dom->documentElement;

        $html = '';

        foreach ($racine->childNodes as $enfant) {
            $html .= $dom->saveHTML($enfant);
        }

        return $html;
    }
}
