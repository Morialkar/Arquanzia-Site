<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marque la date à laquelle le slug d'un livre a été gelé, c'est-à-dire sa première mise en
 * publication. Passé ce point le slug ne change plus : il compose l'URL du livre et de ses
 * chapitres, donc les entrées des flux RSS. Un renommage y casserait les abonnements sans que
 * personne ne le signale — un lecteur qui ne reçoit plus rien n'affiche pas d'erreur.
 *
 * Le verrou est délibérément une date et non un booléen dérivé de `is_published` : dépublier
 * un livre ne doit pas rouvrir son slug, sinon dépublier, renommer et republier contourne la
 * règle et casse quand même les abonnés acquis pendant la première publication.
 *
 * Report : tout livre déjà publié est considéré comme gelé à la date de sa création, la seule
 * dont on dispose rétroactivement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->timestamp('slug_locked_at')->nullable()->after('is_published');
        });

        DB::table('books')
            ->where('is_published', true)
            ->update(['slug_locked_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('slug_locked_at');
        });
    }
};
