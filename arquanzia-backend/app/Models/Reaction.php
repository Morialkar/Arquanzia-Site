<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = ['post_id', 'user_id', 'type'];

    protected $fillable = ['post_id', 'user_id', 'type'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('post_id', $this->getAttribute('post_id'))
            ->where('user_id', $this->getAttribute('user_id'))
            ->where('type', $this->getAttribute('type'));
    }
}
