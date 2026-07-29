<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fragment_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('fragment_nodes')->cascadeOnDelete();
            $table->enum('type', ['category', 'item']);
            $table->string('slug');
            $table->string('title');
            $table->text('description_md')->nullable();
            $table->uuid('thumbnail_media_id')->nullable();
            $table->foreign('thumbnail_media_id')->references('id')->on('post_media')->nullOnDelete();
            $table->integer('order_index')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['parent_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fragment_nodes');
    }
};
