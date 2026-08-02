<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Trace qu'un texte cite une entrée d'encyclopédie via un wikilink.
 */
class Mention extends Model
{
    use HasUuids;

    protected $fillable = ['source_type', 'source_id', 'target_node_id'];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(EncyclopediaNode::class, 'target_node_id');
    }
}
