<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire `posts.audience` (public/connected/vip/reader).
 *
 * Cette colonne cloisonnait le fil selon le palier du visiteur. Les paliers n'existent plus et
 * le fil affiche tous les billets : la colonne ne filtrait donc plus rien, tout en continuant à
 * être proposée au moment de la rédaction. Un billet marqué « VIP » s'affichait publiquement —
 * une promesse de confidentialité que le site ne tenait pas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'audience')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['audience']);
            $table->dropColumn('audience');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'audience')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->enum('audience', ['public', 'connected', 'vip', 'reader'])->default('public');
            $table->index('audience');
        });
    }
};
