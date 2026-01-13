<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entitlement_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['vip', 'reader']);
            $table->timestamp('ends_at')->nullable();
            $table->enum('updated_source', ['admin_manual', 'shopify_order'])->default('admin_manual');
            $table->string('updated_by_admin_email')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type']);
        });

        Schema::create('entitlement_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['vip', 'reader']);
            $table->string('source_ref')->unique();
            $table->integer('months_added')->default(1);
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlement_events');
        Schema::dropIfExists('entitlement_states');
    }
};
