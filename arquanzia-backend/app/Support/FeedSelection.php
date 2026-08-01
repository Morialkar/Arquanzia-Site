<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Sélection demandée par un flux, sous forme normalisée.
 *
 * Chaque combinaison de paramètres est une URL distincte, qu'un lecteur RSS interrogera
 * toutes les quinze à soixante minutes, indéfiniment. Sans normalisation, `?livres=a,b` et
 * `?livres=b,a` seraient deux flux différents pour le même contenu : deux abonnements, deux
 * fois la charge, aucune mise en cache partagée. La forme canonique tranche cela une bonne
 * fois, et le contrôleur redirige vers elle.
 */
class FeedSelection
{
    /** Sections mobilisables en plus des chapitres. */
    public const SECTIONS = ['fil', 'encyclopedie', 'fragments'];

    /** Au-delà, une URL de flux devient un moyen commode de faire travailler le serveur. */
    public const MAX_BOOKS = 20;

    /**
     * @param  list<string>  $books  slugs de livres, triés et dédoublonnés
     * @param  list<string>  $sections  sections, triées et dédoublonnées
     */
    private function __construct(
        public readonly array $books,
        public readonly array $sections,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            self::normalizeList($request->query('livres')),
            array_values(array_intersect(
                self::normalizeList($request->query('sections')),
                self::SECTIONS,
            )),
        );
    }

    /**
     * @param  mixed  $value  chaîne séparée par des virgules, ou tableau
     * @return list<string>
     */
    private static function normalizeList(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        $items = is_array($value) ? $value : explode(',', (string) $value);

        $items = array_map(
            fn ($item) => mb_strtolower(trim((string) $item)),
            Arr::flatten($items),
        );

        $items = array_filter($items, fn (string $item) => $item !== '');
        $items = array_unique($items);
        sort($items);

        return array_values($items);
    }

    /** Aucun sélecteur : le flux couvre tout le site. */
    public function isEverything(): bool
    {
        return $this->books === [] && $this->sections === [];
    }

    public function includesChapters(): bool
    {
        // Les chapitres sont la raison d'être du flux : ils y figurent sauf si la sélection
        // porte explicitement sur d'autres sections.
        return $this->isEverything() || $this->books !== [];
    }

    public function includesSection(string $section): bool
    {
        return $this->isEverything() || in_array($section, $this->sections, true);
    }

    public function exceedsBookLimit(): bool
    {
        return count($this->books) > self::MAX_BOOKS;
    }

    /**
     * Paramètres de requête sous leur forme canonique, prêts pour route().
     *
     * @return array<string, string>
     */
    public function toQuery(): array
    {
        $query = [];

        if ($this->books !== []) {
            $query['livres'] = implode(',', $this->books);
        }

        if ($this->sections !== []) {
            $query['sections'] = implode(',', $this->sections);
        }

        return $query;
    }

    /** La requête reçue est-elle déjà sous forme canonique ? */
    public function matchesQuery(Request $request): bool
    {
        $received = array_filter(
            $request->query(),
            fn (string $key) => in_array($key, ['livres', 'sections'], true),
            ARRAY_FILTER_USE_KEY,
        );

        ksort($received);
        $canonical = $this->toQuery();
        ksort($canonical);

        return $received === $canonical;
    }
}
