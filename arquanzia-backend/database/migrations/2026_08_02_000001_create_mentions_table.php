<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mentions d'une entrée d'encyclopédie par un autre texte.
 *
 * La table existe pour éviter de balayer tout le contenu du site à chaque affichage d'une
 * entrée : sans elle, ouvrir une page d'encyclopédie lirait le markdown de chaque chapitre,
 * article et fragment pour y chercher des wikilinks. Elle est alimentée à l'enregistrement des
 * textes, comme la carte des chemins d'arborescence.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Le texte qui cite : chapitre, entrée d'encyclopédie ou fragment.
            $table->uuidMorphs('source');

            $table->foreignUuid('target_node_id')
                ->constrained('encyclopedia_nodes')
                ->cascadeOnDelete();

            $table->timestamps();

            // Un texte peut citer dix fois la même entrée : on n'en garde qu'une trace.
            $table->unique(['source_type', 'source_id', 'target_node_id'], 'mentions_unicite');
            $table->index('target_node_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentions');
    }
};
