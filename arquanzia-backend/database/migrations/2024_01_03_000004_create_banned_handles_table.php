<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banned_handles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('handle')->unique();
            $table->uuid('original_user_id')->nullable();
            $table->string('banned_by_email');
            $table->timestamps();

            $table->foreign('original_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('handle_ban_count')->default(0)->after('handle');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('handle_ban_count');
        });
        Schema::dropIfExists('banned_handles');
    }
};
