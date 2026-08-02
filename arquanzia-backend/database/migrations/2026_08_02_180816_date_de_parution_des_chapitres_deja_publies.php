<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Comble les dates de parution laissées vides.
 *
 * Le formulaire annonçait « Laisser vide = immédiat », mais le contrôleur enregistrait la
 * saisie telle quelle : tout chapitre publié sans qu'on touche au champ gardait une date
 * nulle. Rien ne le montrait sur sa fiche, mais MySQL range les NULL en dernier, si bien que
 * ces chapitres tombaient au fond de la page d'accueil comme du flux Atom — une parution du
 * jour passait derrière un chapitre d'il y a un an.
 *
 * Le contrôleur tient désormais sa promesse ; restent les enregistrements déjà en base, que
 * cette migration date de leur création. C'est l'approximation la plus juste disponible : rien
 * n'a conservé le moment où la case « publié » a été cochée.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('chapters')
            ->where('is_published', true)
            ->whereNull('published_at')
            ->update(['published_at' => DB::raw('created_at')]);
    }

    /**
     * Sans retour en arrière : plus rien ne distinguerait les dates comblées ici de celles qui
     * ont toujours été saisies, et les remettre à vide recréerait le défaut.
     */
    public function down(): void {}
};
