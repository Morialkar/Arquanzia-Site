<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notes d'autrice ancrées à un paragraphe.
 *
 * L'ancrage repose sur les identifiants posés par `App\Support\ParagraphAnchors`, dérivés du
 * contenu du paragraphe. Réécrire un paragraphe change donc son identifiant et détache sa
 * note : celle-ci est conservée et signalée dans l'administration, jamais rattachée
 * automatiquement. Une note replacée au mauvais endroit serait pire qu'une note visiblement
 * orpheline, et seule l'autrice sait où elle devait aller.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Le morphisme couvre chapitres et articles d'encyclopédie sans table par type.
            $table->uuidMorphs('notable');

            // Identifiant du paragraphe visé, tel que ParagraphAnchors le calcule.
            $table->string('paragraph_id', 32);

            $table->text('note_md');
            $table->timestamps();

            $table->index(['notable_type', 'notable_id', 'paragraph_id'], 'author_notes_ancrage_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_notes');
    }
};
