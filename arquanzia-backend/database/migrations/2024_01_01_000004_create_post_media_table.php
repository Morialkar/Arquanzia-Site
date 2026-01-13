<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('post_id')->nullable();
            $table->integer('position')->default(0);
            $table->string('original_path')->nullable();
            $table->string('locked_path')->nullable();
            $table->string('mime')->nullable();
            $table->string('filename')->nullable();
            $table->integer('original_width')->nullable();
            $table->integer('original_height')->nullable();
            $table->integer('locked_width')->nullable();
            $table->integer('locked_height')->nullable();
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts')->nullOnDelete();
            $table->index(['post_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
