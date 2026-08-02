<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Date de dernière révision d'un texte.
 *
 * Distincte de `updated_at`, qui bouge pour des raisons invisibles au lecteur : une vignette
 * changée, un ordre d'affichage ajusté, une publication basculée. Seule une modification du
 * texte lui-même compte comme révision.
 *
 * Le plan envisageait une case « révision notable » cochée à la main. Détecter la modification
 * du contenu est plus fiable : rien à cocher, rien à oublier, et une correction de vignette ne
 * fait pas remonter une entrée comme si elle avait été réécrite.
 */
return new class extends Migration
{
    private const TABLES = ['chapters', 'encyclopedia_nodes', 'fragment_nodes'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->timestamp('revised_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('revised_at');
            });
        }
    }
};
