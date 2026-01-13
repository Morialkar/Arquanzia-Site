<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('delivery_email_id');
            $table->uuid('book_id');
            $table->uuid('chapter_id');
            $table->string('format_sent');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('delivery_email_id')->references('id')->on('delivery_emails')->cascadeOnDelete();
            $table->foreign('book_id')->references('id')->on('books')->cascadeOnDelete();
            $table->foreign('chapter_id')->references('id')->on('chapters')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_jobs');
    }
};
