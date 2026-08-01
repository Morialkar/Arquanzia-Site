<?php

namespace App\Services;

use App\Helpers\MarkdownHelper;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use App\Support\FeedSelection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Rassemble les entrées d'un flux de syndication.
 *
 * Le flux transporte le texte intégral : le contenu est déjà public, un agrégateur n'est
 * qu'un navigateur de plus, et la licence s'applique de la même façon. Le bandeau
 * promotionnel d'un chapitre est rendu à la suite du texte, pour qu'il voyage avec lui.
 */
class FeedBuilder
{
    public const MAX_ENTRIES = 20;

    /**
     * @return Collection<int, array{title: string, url: string, updated: \Illuminate\Support\Carbon, published: \Illuminate\Support\Carbon, summary: ?string, content: ?string, category: string}>
     */
    public function entries(FeedSelection $selection): Collection
    {
        $entries = collect();

        if ($selection->includesChapters()) {
            $entries = $entries->concat($this->chapters($selection));
        }

        if ($selection->includesSection('fil')) {
            $entries = $entries->concat($this->posts());
        }

        if ($selection->includesSection('encyclopedie')) {
            $entries = $entries->concat($this->encyclopedia());
        }

        if ($selection->includesSection('fragments')) {
            $entries = $entries->concat($this->fragments());
        }

        return $entries
            ->sortByDesc(fn (array $entry) => $entry['published'])
            ->values()
            ->take(self::MAX_ENTRIES);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function chapters(FeedSelection $selection): Collection
    {
        $query = Chapter::query()
            ->where('is_published', true)
            ->whereHas('book', fn ($q) => $q->published())
            ->with('book:id,slug,title');

        if ($selection->books !== []) {
            $query->whereHas('book', fn ($q) => $q->whereIn('slug', $selection->books));
        }

        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(self::MAX_ENTRIES)
            ->get()
            // Un chapitre daté du futur n'est pas encore paru : il ne doit pas apparaître
            // dans un flux avant sa date de parution.
            ->reject(fn (Chapter $chapter) => $chapter->isComingSoon())
            ->map(fn (Chapter $chapter) => [
                'title' => $chapter->title.' — '.$chapter->book->title,
                'url' => route('library.chapter', [$chapter->book->slug, $chapter->slug]),
                'updated' => $chapter->updated_at,
                'published' => $chapter->published_at ?? $chapter->created_at,
                'summary' => $this->excerpt($chapter->content_md),
                'content' => $this->absolutize(
                    $this->renderMarkdown($chapter->content_md).$this->promoBanner($chapter)
                ),
                'category' => 'Chapitre',
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function posts(): Collection
    {
        return Post::query()
            ->orderByDesc('created_at')
            ->limit(self::MAX_ENTRIES)
            ->get()
            ->map(fn (Post $post) => [
                'title' => $post->title ?: 'Sans titre',
                'url' => route('post.show', $post),
                'updated' => $post->updated_at,
                'published' => $post->created_at,
                'summary' => $post->preview_text,
                'content' => $this->absolutize($post->content_full_html),
                'category' => 'Fil',
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function encyclopedia(): Collection
    {
        return EncyclopediaNode::published()
            ->articles()
            ->with('article')
            ->orderByDesc('created_at')
            ->limit(self::MAX_ENTRIES)
            ->get()
            ->map(fn (EncyclopediaNode $node) => [
                'title' => $node->title,
                'url' => route('encyclopedia.show', $node->getFullPath()),
                'updated' => $node->updated_at,
                'published' => $node->created_at,
                'summary' => $this->excerpt($node->teaser_md),
                'content' => $this->absolutize($node->article?->content_html ?? $node->teaser_html),
                'category' => 'Encyclopédie',
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function fragments(): Collection
    {
        return FragmentNode::query()
            ->where('is_published', true)
            ->where('type', 'item')
            ->orderByDesc('created_at')
            ->limit(self::MAX_ENTRIES)
            ->get()
            ->map(fn (FragmentNode $node) => [
                'title' => $node->title,
                'url' => route('fragments.show', $node->getFullPath()),
                'updated' => $node->updated_at,
                'published' => $node->created_at,
                'summary' => $this->excerpt($node->description_md),
                'content' => $this->absolutize($node->description_html),
                'category' => 'Fragment',
            ]);
    }

    /** Vérifie qu'au moins un livre demandé existe, pour distinguer un flux vide d'une erreur. */
    public function unknownBooks(FeedSelection $selection): array
    {
        if ($selection->books === []) {
            return [];
        }

        $known = Book::published()
            ->whereIn('slug', $selection->books)
            ->pluck('slug')
            ->all();

        return array_values(array_diff($selection->books, $known));
    }

    private function renderMarkdown(?string $markdown): string
    {
        return $markdown ? MarkdownHelper::render($markdown) : '';
    }

    private function excerpt(?string $markdown): ?string
    {
        if (! $markdown) {
            return null;
        }

        return Str::limit(trim(strip_tags($this->renderMarkdown($markdown))), 300);
    }

    private function promoBanner(Chapter $chapter): string
    {
        if (! $chapter->promo_banner_enabled || ! $chapter->promo_banner_text) {
            return '';
        }

        $text = e($chapter->promo_banner_text);

        if (! $chapter->promo_banner_button_url) {
            return '<hr /><p>'.$text.'</p>';
        }

        return '<hr /><p>'.$text.' <a href="'.e($chapter->promo_banner_button_url).'">'
            .e($chapter->promo_banner_button_label ?: 'En savoir plus')
            .'</a></p>';
    }

    /**
     * Rend absolues les adresses relatives du HTML.
     *
     * Hors du site, un chemin comme /media/42 ne mène nulle part : le lecteur RSS n'a aucune
     * base à laquelle le rattacher. Les images disparaissent et les liens cassent.
     */
    private function absolutize(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        return preg_replace_callback(
            '/\b(href|src)=(["\'])\/(?!\/)([^"\']*)\2/i',
            fn (array $m) => $m[1].'='.$m[2].url('/'.$m[3]).$m[2],
            $html,
        );
    }
}
