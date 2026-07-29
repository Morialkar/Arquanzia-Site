<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fragment_items', function (Blueprint $table) {
            $table->uuid('node_id')->primary();
            $table->foreign('node_id')->references('id')->on('fragment_nodes')->cascadeOnDelete();
            $table->uuid('media_id')->nullable();
            $table->foreign('media_id')->references('id')->on('post_media')->nullOnDelete();
            $table->string('video_url')->nullable();
            $table->enum('media_type', ['image', 'video', 'pdf', 'coloring']);
            $table->boolean('is_downloadable')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fragment_items');
    }
};
