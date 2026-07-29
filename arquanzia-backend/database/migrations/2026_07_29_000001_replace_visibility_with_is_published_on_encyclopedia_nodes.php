<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le palier « Lecteur » n'existe plus : il n'y a pas de compte lecteur et tout le contenu
 * est en lecture publique. La visibilité d'un nœud devient donc un simple brouillon/publié,
 * aligné sur le `is_published` des livres et des chapitres.
 *
 * Report : les nœuds « public » deviennent publiés, les nœuds « reader » deviennent des
 * brouillons. Ces derniers étaient déjà exclus de la recherche, du sitemap et de l'accueil ;
 * les traiter comme des brouillons ne publie donc rien de nouveau, et il suffit de les
 * repasser en publié depuis le back-office pour les rendre visibles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encyclopedia_nodes', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('type');
        });

        DB::table('encyclopedia_nodes')
            ->where('visibility', 'reader')
            ->update(['is_published' => false]);

        Schema::table('encyclopedia_nodes', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('encyclopedia_nodes', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'reader'])->default('public')->after('type');
        });

        DB::table('encyclopedia_nodes')
            ->where('is_published', false)
            ->update(['visibility' => 'reader']);

        Schema::table('encyclopedia_nodes', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
