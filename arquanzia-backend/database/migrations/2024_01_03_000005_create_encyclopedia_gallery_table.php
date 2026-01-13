<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encyclopedia_gallery', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('article_id');
            $table->uuid('media_id');
            $table->integer('order_index')->default(0);
            $table->string('caption')->nullable();
            $table->timestamps();

            $table->foreign('article_id')->references('node_id')->on('encyclopedia_articles')->cascadeOnDelete();
            $table->foreign('media_id')->references('id')->on('post_media')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encyclopedia_gallery');
    }
};
