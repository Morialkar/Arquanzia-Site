<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un compte ne sert qu'à l'accès au back-office : le site n'a pas de login lecteur et tout le
 * contenu est en lecture publique. L'authentification passe par un lien magique et la session,
 * sans mot de passe ni contrat Authenticatable.
 */
class User extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'handle',
        'email',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_user_id');
    }
}
