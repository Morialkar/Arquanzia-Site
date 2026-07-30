<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire `chapters.is_public`, vestige des paliers d'accès : la colonne n'était lue nulle
 * part, absente du $fillable du modèle — les deux écritures du contrôleur étaient donc
 * silencieusement ignorées. `is_published` est la seule règle d'accès.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('chapters', 'is_public')) {
            return;
        }

        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('chapters', 'is_public')) {
            return;
        }

        Schema::table('chapters', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('is_published');
        });
    }
};
