<?php

namespace App\Models\Concerns;

/**
 * Marque la date de révision quand le texte lui-même change.
 *
 * `updated_at` ne convient pas : il bouge dès qu'on change une vignette, un ordre d'affichage
 * ou un statut de publication. Un lecteur qui suit les révisions verrait alors remonter des
 * entrées auxquelles rien n'a été ajouté.
 *
 * La création n'est pas une révision : un texte paru hier est une nouveauté, et le flux RSS
 * l'annonce déjà.
 */
trait TracksRevisions
{
    /** Le cast vit dans le trait : les trois modèles qui l'emploient n'ont rien à déclarer. */
    public function initializeTracksRevisions(): void
    {
        $this->casts['revised_at'] = 'datetime';
    }

    public static function bootTracksRevisions(): void
    {
        static::updating(function ($model) {
            foreach ($model->revisableFields() as $champ) {
                if ($model->isDirty($champ)) {
                    $model->revised_at = now();

                    return;
                }
            }
        });
    }

    /**
     * Champs dont la modification constitue une révision.
     *
     * @return list<string>
     */
    abstract public function revisableFields(): array;

    public function markRevised(): void
    {
        $this->forceFill(['revised_at' => now()])->saveQuietly();
    }
}
