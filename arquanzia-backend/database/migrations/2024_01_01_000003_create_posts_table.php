<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('author_user_id');
            $table->enum('audience', ['public', 'connected', 'vip', 'reader']);
            $table->string('title');
            $table->text('preview_text');
            $table->text('content_full')->nullable();
            $table->timestamps();

            $table->foreign('author_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('created_at');
            $table->index('audience');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
