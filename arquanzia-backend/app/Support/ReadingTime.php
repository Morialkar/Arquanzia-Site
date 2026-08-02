<?php

namespace App\Support;

/**
 * Longueur d'un texte et durée de lecture estimée.
 *
 * Répond à la seule question qu'on se pose avant d'ouvrir un texte long : est-ce que je
 * m'engage pour cinq minutes ou pour une heure.
 *
 * Rien n'est stocké en base. Le calcul est instantané, et une colonne se désynchroniserait à
 * la première modification du texte — le même piège que les chemins d'arborescence, où une
 * valeur figée aurait menti dès le premier déplacement de nœud.
 */
class ReadingTime
{
    /**
     * Mots par minute pour de la prose française.
     *
     * La fourchette usuelle va de 200 à 250. On retient la borne basse : mieux vaut annoncer
     * quinze minutes pour une lecture qui en prend douze que l'inverse.
     */
    private const WORDS_PER_MINUTE = 200;

    public static function of(?string $markdown): self
    {
        return new self(self::countWords($markdown));
    }

    /** @param  iterable<string|null>  $texts */
    public static function ofMany(iterable $texts): self
    {
        $total = 0;

        foreach ($texts as $text) {
            $total += self::countWords($text);
        }

        return new self($total);
    }

    private function __construct(
        public readonly int $words,
    ) {}

    public function isEmpty(): bool
    {
        return $this->words === 0;
    }

    public function minutes(): int
    {
        return (int) max(1, ceil($this->words / self::WORDS_PER_MINUTE));
    }

    /** Formulation courte, destinée à être affichée près d'un titre. */
    public function label(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        $minutes = $this->minutes();
        $mots = number_format($this->words, 0, ',', ' ');

        return $this->words < self::WORDS_PER_MINUTE
            ? "moins d’une minute · {$mots} mots"
            : "{$minutes} min · {$mots} mots";
    }

    /**
     * Compte les mots d'un texte Markdown.
     *
     * Le balisage est retiré avant le comptage : sans cela, les adresses des liens et les
     * attributs HTML du contenu gonflent le total sans que personne ne les lise.
     */
    private static function countWords(?string $markdown): int
    {
        if ($markdown === null || trim($markdown) === '') {
            return 0;
        }

        $text = $markdown;

        // Les blocs de code ne se lisent pas au rythme de la prose, et le site n'en publie pas.
        $text = preg_replace('/```.*?```/s', ' ', $text) ?? $text;
        $text = preg_replace('/`[^`]*`/', ' ', $text) ?? $text;

        // Les images d'abord : leur syntaxe englobe celle des liens, et les traiter après
        // laisserait le point d'exclamation et le texte alternatif dans le compte.
        $text = preg_replace('/!\[[^\]]*\]\([^)]*\)/', ' ', $text) ?? $text;
        // Un lien devient son libellé : l'adresse ne se lit pas.
        $text = preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $text) ?? $text;
        // Un wikilink devient son libellé, ou son terme s'il n'en a pas.
        $text = preg_replace('/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/', '$2$1', $text) ?? $text;

        $text = strip_tags($text);

        // Reste le balisage d'emphase et de structure, qui n'est pas du mot.
        $text = str_replace(['*', '_', '#', '>', '|', '~'], ' ', $text);

        $mots = preg_split('/[\s\x{00A0}]+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        return $mots === false ? 0 : count($mots);
    }
}
