<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bridge_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token_hash', 64)->unique();
            $table->string('shopify_customer_id');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index('shopify_customer_id');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bridge_tokens');
    }
};
