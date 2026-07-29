<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wikilinks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('term')->unique();
            $table->uuid('encyclopedia_node_id')->nullable();
            $table->string('custom_url')->nullable();
            $table->timestamps();

            $table->foreign('encyclopedia_node_id')
                ->references('id')
                ->on('encyclopedia_nodes')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wikilinks');
    }
};
