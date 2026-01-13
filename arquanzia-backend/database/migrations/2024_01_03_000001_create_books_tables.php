<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description_md')->nullable();
            $table->uuid('cover_media_id')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->foreign('cover_media_id')->references('id')->on('post_media')->nullOnDelete();
        });

        Schema::create('book_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_id');
            $table->enum('format', ['pdf', 'epub']);
            $table->uuid('file_media_id');
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->cascadeOnDelete();
            $table->foreign('file_media_id')->references('id')->on('post_media')->cascadeOnDelete();
            $table->unique(['book_id', 'format']);
        });

        Schema::create('chapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_id');
            $table->string('slug');
            $table->string('title');
            $table->integer('order_index')->default(0);
            $table->longText('content_md')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->cascadeOnDelete();
            $table->unique(['book_id', 'slug']);
        });

        Schema::create('chapter_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('chapter_id');
            $table->enum('format', ['pdf', 'epub']);
            $table->uuid('file_media_id');
            $table->timestamps();

            $table->foreign('chapter_id')->references('id')->on('chapters')->cascadeOnDelete();
            $table->foreign('file_media_id')->references('id')->on('post_media')->cascadeOnDelete();
            $table->unique(['chapter_id', 'format']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_files');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('book_files');
        Schema::dropIfExists('books');
    }
};
