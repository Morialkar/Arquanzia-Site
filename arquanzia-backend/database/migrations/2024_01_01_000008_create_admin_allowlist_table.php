<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_allowlist', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'editor'])->default('editor');
            $table->string('created_by_email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_allowlist');
    }
};
