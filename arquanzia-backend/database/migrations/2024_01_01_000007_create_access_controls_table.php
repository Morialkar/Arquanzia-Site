<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_controls', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->boolean('is_readonly')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->text('ban_reason')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('vip_until')->nullable();
            $table->timestamp('reader_until')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_controls');
    }
};
