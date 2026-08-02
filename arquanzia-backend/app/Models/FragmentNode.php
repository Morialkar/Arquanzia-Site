<?php

namespace App\Models;

use App\Helpers\MarkdownHelper;
use App\Models\Concerns\HasTreePath;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FragmentNode extends Model
{
    use HasFactory, HasTreePath, HasUuids;

    protected $fillable = [
        'parent_id',
        'type',
        'slug',
        'title',
        'description_md',
        'thumbnail_media_id',
        'order_index',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FragmentNode::class, 'parent_id');
    }

    protected static function booted(): void
    {
        // Le morphisme ne peut pas porter de contrainte de clé étrangère du côté source :
        // sans ce nettoyage, les mentions d'un fragment supprimé resteraient en base et
        // renverraient à un texte disparu.
        static::deleting(function ($model) {
            $model->mentions()->delete();
        });
    }

    /** Entrées d'encyclopédie que ce texte cite. */
    public function mentions(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Mention::class, 'source');
    }

    public function children(): HasMany
    {
        return $this->hasMany(FragmentNode::class, 'parent_id')->orderBy('order_index');
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(PostMedia::class, 'thumbnail_media_id');
    }

    public function item(): HasOne
    {
        return $this->hasOne(FragmentItem::class, 'node_id');
    }

    public function isCategory(): bool
    {
        return $this->type === 'category';
    }

    public function isItem(): bool
    {
        return $this->type === 'item';
    }

    public function getDescriptionHtmlAttribute(): ?string
    {
        if (! $this->description_md) {
            return null;
        }

        return MarkdownHelper::render($this->description_md);
    }

    public function ancestors(): array
    {
        $ancestors = [];
        $node = $this->parent;

        while ($node) {
            array_unshift($ancestors, $node);
            $node = $node->parent;
        }

        return $ancestors;
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('order_index');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
