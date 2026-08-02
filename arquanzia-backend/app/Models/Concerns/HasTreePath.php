<?php

namespace App\Models\Concerns;

/**
 * Chemin complet d'un nœud dans son arborescence.
 *
 * La version naïve remontait les parents un par un, chacun déclenchant sa propre requête.
 * Multiplié par le nombre de nœuds d'un flux, d'une recherche ou d'un sitemap, cela produisait
 * une croissance linéaire du nombre de requêtes : seize pour un flux clairsemé, quarante-six
 * dès que le contenu triplait. Le flux étant interrogé toutes les quinze à soixante minutes
 * par chaque abonné, indéfiniment, c'est le seul endroit du site où ce coût s'accumule.
 *
 * La correspondance identifiant → (slug, parent) est désormais chargée une fois par requête
 * HTTP, en une seule interrogation ne ramenant que trois colonnes. Le gain vaut dès le premier
 * nœud résolu, un chemin de profondeur trois coûtant sinon trois requêtes à lui seul.
 */
trait HasTreePath
{
    /** @var array<string, array{slug: string, parent_id: ?string}>|null */
    protected static ?array $treePathCache = null;

    /** Toute écriture invalide la carte : un déplacement de nœud change des chemins. */
    public static function bootHasTreePath(): void
    {
        static::saved(fn () => static::$treePathCache = null);
        static::deleted(fn () => static::$treePathCache = null);
    }

    /** @return array<string, array{slug: string, parent_id: ?string}> */
    protected static function treePathMap(): array
    {
        return static::$treePathCache ??= static::query()
            ->select(['id', 'parent_id', 'slug'])
            ->get()
            ->mapWithKeys(fn ($node) => [
                $node->id => ['slug' => $node->slug, 'parent_id' => $node->parent_id],
            ])
            ->all();
    }

    public function getFullPath(): string
    {
        $map = static::treePathMap();
        $segments = [];
        $id = $this->id;

        // Garde-fou contre une chaîne de parents circulaire, qu'un déplacement malencontreux
        // pourrait créer : sans elle, la boucle ne se terminerait jamais.
        $depth = 0;

        while ($id !== null && isset($map[$id]) && $depth++ < 32) {
            array_unshift($segments, $map[$id]['slug']);
            $id = $map[$id]['parent_id'];
        }

        return $segments === [] ? (string) $this->slug : implode('/', $segments);
    }
}
