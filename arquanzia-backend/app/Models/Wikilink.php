<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Wikilink extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'term',
        'encyclopedia_node_id',
        'custom_url',
    ];

    public function encyclopediaNode(): BelongsTo
    {
        return $this->belongsTo(EncyclopediaNode::class);
    }

    public function getUrl(): ?string
    {
        if ($this->custom_url) {
            return $this->custom_url;
        }

        if ($this->encyclopediaNode) {
            return route('encyclopedia.show', $this->encyclopediaNode->getFullPath());
        }

        return null;
    }

    public static function findByTerm(string $term): ?self
    {
        return static::where('term', $term)->first();
    }

    public static function resolveTarget(string $term): ?array
    {
        try {
            $wikilink = static::with('encyclopediaNode')->where('term', $term)->first();
            if ($wikilink) {
                if ($wikilink->custom_url) {
                    return [
                        'url' => $wikilink->custom_url,
                        'teaser' => null,
                        'title' => $term,
                    ];
                }

                if ($wikilink->encyclopediaNode) {
                    return [
                        'url' => route('encyclopedia.show', $wikilink->encyclopediaNode->getFullPath()),
                        'teaser' => self::extractTeaser($wikilink->encyclopediaNode),
                        'title' => $wikilink->encyclopediaNode->title,
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        $node = EncyclopediaNode::where('title', $term)
            ->orWhere('slug', Str::slug($term))
            ->with('article')
            ->first();

        if ($node) {
            return [
                'url' => route('encyclopedia.show', $node->getFullPath()),
                'teaser' => self::extractTeaser($node),
                'title' => $node->title,
            ];
        }

        return null;
    }

    public static function resolveUrl(string $term): ?string
    {
        $target = static::resolveTarget($term);
        return $target['url'] ?? null;
    }

    protected static function extractTeaser(EncyclopediaNode $node): ?string
    {
        $teaser = $node->teaser_html;
        if (!$teaser && $node->article?->content_md) {
            $teaser = Str::limit(strip_tags($node->article->content_md), 140);
        }

        return $teaser ? Str::limit(strip_tags($teaser), 180) : null;
    }
}
