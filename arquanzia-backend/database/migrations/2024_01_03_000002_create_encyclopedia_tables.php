<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encyclopedia_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->enum('type', ['category', 'article']);
            $table->string('slug');
            $table->string('title');
            $table->enum('visibility', ['public', 'reader'])->default('public');
            $table->text('teaser_md')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('encyclopedia_nodes')->cascadeOnDelete();
            $table->unique(['parent_id', 'slug']);
        });

        Schema::create('encyclopedia_articles', function (Blueprint $table) {
            $table->uuid('node_id')->primary();
            $table->longText('content_md')->nullable();
            $table->uuid('cover_media_id')->nullable();
            $table->timestamps();

            $table->foreign('node_id')->references('id')->on('encyclopedia_nodes')->cascadeOnDelete();
            $table->foreign('cover_media_id')->references('id')->on('post_media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encyclopedia_articles');
        Schema::dropIfExists('encyclopedia_nodes');
    }
};
