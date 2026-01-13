<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('viewable_type'); // book, chapter, encyclopedia
            $table->uuid('viewable_id');
            $table->uuid('user_id')->nullable();
            $table->date('viewed_date');
            $table->timestamps();

            $table->index(['viewable_type', 'viewable_id', 'viewed_date']);
            $table->index(['viewed_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
