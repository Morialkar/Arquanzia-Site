<?php

namespace App\Providers;

use App\Models\Chapter;
use App\Models\EncyclopediaArticle;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Services\MentionIndexer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->indexerLesMentions();
    }

    /**
     * Les mentions se recalculent à l'enregistrement d'un texte, jamais à l'affichage : sans
     * cela, chaque page d'encyclopédie lirait le markdown de tout le site pour y chercher les
     * wikilinks qui la citent.
     */
    private function indexerLesMentions(): void
    {
        foreach ([Chapter::class, EncyclopediaNode::class, FragmentNode::class] as $classe) {
            $classe::saved(fn ($source) => app(MentionIndexer::class)->index($source));
        }

        // Le corps d'un article vit dans une table à part : le modifier doit réindexer son
        // nœud, seul porteur d'une adresse et donc seule source utile.
        EncyclopediaArticle::saved(function (EncyclopediaArticle $article) {
            if (! $node = EncyclopediaNode::find($article->node_id)) {
                return;
            }

            app(MentionIndexer::class)->index($node);

            // Le corps de l'article est le texte que lisent les visiteurs : le modifier est
            // une révision de l'entrée, même si le nœud lui-même n'a pas bougé.
            if ($article->wasChanged('content_md')) {
                $node->markRevised();
            }
        });
    }
}
