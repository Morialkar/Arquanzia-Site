<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Mention;
use App\Models\Wikilink;
use Illuminate\Database\Eloquent\Model;

/**
 * Tient à jour la table des mentions.
 *
 * Chaque texte déclare les entrées d'encyclopédie qu'il cite par wikilink. L'indexation a lieu
 * à l'enregistrement : la calculer à l'affichage obligerait chaque page d'encyclopédie à lire
 * le markdown de tout le site.
 */
class MentionIndexer
{
    /** Textes susceptibles de citer une entrée. */
    public const SOURCES = [Chapter::class, EncyclopediaNode::class, FragmentNode::class];

    /** Réindexe un texte : ses mentions sont remplacées par celles de son contenu actuel. */
    public function index(Model $source): void
    {
        $cibles = $this->targetsOf($source);

        $source->mentions()->whereNotIn('target_node_id', $cibles ?: ['-'])->delete();

        foreach ($cibles as $cible) {
            Mention::firstOrCreate([
                'source_type' => $source->getMorphClass(),
                'source_id' => $source->getKey(),
                'target_node_id' => $cible,
            ]);
        }
    }

    /** Reconstruit tout l'index — après un import, ou pour rattraper un désaccord. */
    public function rebuild(): int
    {
        Mention::query()->delete();
        $total = 0;

        foreach (self::SOURCES as $classe) {
            foreach ($classe::cursor() as $source) {
                $this->index($source);
                $total++;
            }
        }

        return $total;
    }

    /**
     * Identifiants des entrées citées par un texte.
     *
     * @return list<string>
     */
    private function targetsOf(Model $source): array
    {
        $termes = $this->termsIn($this->contentOf($source));
        $cibles = [];

        foreach ($termes as $terme) {
            $node = Wikilink::resolveNode($terme);

            // Une entrée qui se cite elle-même ne s'apprend rien.
            if (! $node || ($source instanceof EncyclopediaNode && $node->is($source))) {
                continue;
            }

            $cibles[$node->getKey()] = true;
        }

        return array_keys($cibles);
    }

    private function contentOf(Model $source): string
    {
        return match (true) {
            $source instanceof Chapter => (string) $source->content_md,
            // Le corps de l'article et son aperçu peuvent tous deux citer.
            $source instanceof EncyclopediaNode => (string) $source->teaser_md."\n".(string) $source->article?->content_md,
            $source instanceof FragmentNode => (string) $source->description_md,
            default => '',
        };
    }

    /**
     * Termes cités, en tenant compte de la forme `[[Terme|libellé]]` où seul le terme compte.
     *
     * @return list<string>
     */
    private function termsIn(string $markdown): array
    {
        if (trim($markdown) === '') {
            return [];
        }

        preg_match_all('/\[\[([^\]|]+)(?:\|[^\]]+)?\]\]/', $markdown, $matches);

        return array_values(array_unique(array_map('trim', $matches[1] ?? [])));
    }
}
