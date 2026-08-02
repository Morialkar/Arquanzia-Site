<?php

namespace App\Models;

use App\Helpers\MarkdownHelper;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Commentaire d'autrice accroché à un paragraphe précis d'un chapitre ou d'un article.
 */
class AuthorNote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['notable_type', 'notable_id', 'paragraph_id', 'note_md'];

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getNoteHtmlAttribute(): string
    {
        return MarkdownHelper::render($this->note_md);
    }
}
