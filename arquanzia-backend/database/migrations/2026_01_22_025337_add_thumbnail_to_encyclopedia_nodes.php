<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('encyclopedia_nodes', function (Blueprint $table) {
            $table->uuid('thumbnail_media_id')->nullable()->after('teaser_md');
            $table->foreign('thumbnail_media_id')->references('id')->on('post_media')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encyclopedia_nodes', function (Blueprint $table) {
            $table->dropForeign(['thumbnail_media_id']);
            $table->dropColumn('thumbnail_media_id');
        });
    }
};
