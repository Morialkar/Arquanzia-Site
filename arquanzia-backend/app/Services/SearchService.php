<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\EncyclopediaNode;
use App\Models\FragmentNode;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Recherche sur l'ensemble du site.
 *
 * La version précédente n'interrogeait que les titres, et seulement ceux des livres, des
 * chapitres et de l'encyclopédie : le fil et les fragments étaient introuvables, et chercher
 * un mot figurant dans un chapitre ne donnait rien. C'était une limite fonctionnelle, pas de
 * performance — le site compte quelques dizaines de documents, où un LIKE reste instantané.
 *
 * Le classement place les correspondances de titre avant celles de contenu : qui cherche
 * « Thalria » veut la page Thalria, pas les chapitres qui la mentionnent.
 */
class SearchService
{
    public const MIN_LENGTH = 2;

    private const SCORE_TITLE = 100;

    private const SCORE_BODY = 10;

    /**
     * @return Collection<int, array{type: string, label: string, title: string, url: string, excerpt: ?string, score: int}>
     */
    public function search(string $query, int $limit = 30): Collection
    {
        $query = trim($query);

        if (mb_strlen($query) < self::MIN_LENGTH) {
            return collect();
        }

        return collect()
            ->concat($this->books($query))
            ->concat($this->chapters($query))
            ->concat($this->encyclopedia($query))
            ->concat($this->fragments($query))
            ->concat($this->posts($query))
            ->sortByDesc('score')
            ->values()
            ->take($limit);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function books(string $query): Collection
    {
        return Book::published()
            ->where(fn ($q) => $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('description_md', 'LIKE', "%{$query}%"))
            ->with('cover')
            ->get()
            ->map(fn (Book $book) => $this->entry(
                'book', 'Livre', $book->title,
                route('library.book', $book->slug),
                $book->description_md, $query,
                $book->cover ? route('media.show', $book->cover->id) : null,
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    private function chapters(string $query): Collection
    {
        return Chapter::where('is_published', true)
            ->whereHas('book', fn ($q) => $q->published())
            ->where(fn ($q) => $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('content_md', 'LIKE', "%{$query}%"))
            ->with('book:id,slug,title')
            ->get()
            ->reject(fn (Chapter $chapter) => $chapter->isComingSoon())
            ->map(fn (Chapter $chapter) => $this->entry(
                'chapter', 'Chapitre', $chapter->title,
                route('library.chapter', [$chapter->book->slug, $chapter->slug]),
                $chapter->content_md, $query, null,
                $chapter->book->title,
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    private function encyclopedia(string $query): Collection
    {
        return EncyclopediaNode::published()
            ->where(fn ($q) => $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('teaser_md', 'LIKE', "%{$query}%")
                ->orWhereHas('article', fn ($a) => $a->where('content_md', 'LIKE', "%{$query}%")))
            ->with('article')
            ->get()
            ->map(fn (EncyclopediaNode $node) => $this->entry(
                'encyclopedia', 'Encyclopédie', $node->title,
                route('encyclopedia.show', $node->getFullPath()),
                $node->article?->content_md ?: $node->teaser_md, $query,
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    private function fragments(string $query): Collection
    {
        return FragmentNode::where('is_published', true)
            ->where(fn ($q) => $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('description_md', 'LIKE', "%{$query}%"))
            ->get()
            ->map(fn (FragmentNode $node) => $this->entry(
                'fragment', 'Fragment', $node->title,
                route('fragments.show', $node->getFullPath()),
                $node->description_md, $query,
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    private function posts(string $query): Collection
    {
        return Post::where(fn ($q) => $q->where('title', 'LIKE', "%{$query}%")
            ->orWhere('preview_text', 'LIKE', "%{$query}%")
            ->orWhere('content_full', 'LIKE', "%{$query}%"))
            ->get()
            ->map(fn (Post $post) => $this->entry(
                'post', 'Fil', $post->title ?: 'Sans titre',
                route('post.show', $post),
                $post->content_full ?: $post->preview_text, $query,
            ));
    }

    /** @return array<string, mixed> */
    private function entry(
        string $type,
        string $label,
        string $title,
        string $url,
        ?string $body,
        string $query,
        ?string $thumbnail = null,
        ?string $context = null,
    ): array {
        $inTitle = Str::contains($title, $query, ignoreCase: true);

        return [
            'type' => $type,
            'label' => $label,
            'title' => $title,
            'context' => $context,
            'url' => $url,
            'thumbnail' => $thumbnail,
            'excerpt' => $inTitle ? null : $this->excerpt($body, $query),
            'score' => $inTitle ? self::SCORE_TITLE : self::SCORE_BODY,
        ];
    }

    /**
     * Extrait le passage entourant la première occurrence.
     *
     * Sans cela, un résultat trouvé dans le corps n'expliquerait pas pourquoi il est là : le
     * titre seul ne contient pas le terme cherché.
     */
    private function excerpt(?string $body, string $query, int $radius = 90): ?string
    {
        if (! $body) {
            return null;
        }

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($body)) ?? '');
        $position = mb_stripos($text, $query);

        if ($position === false) {
            return Str::limit($text, $radius * 2);
        }

        $start = max(0, $position - $radius);
        $excerpt = mb_substr($text, $start, $radius * 2 + mb_strlen($query));

        return ($start > 0 ? '… ' : '').$excerpt.($start + mb_strlen($excerpt) < mb_strlen($text) ? ' …' : '');
    }
}
