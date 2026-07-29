<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('reactions');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('reading_progress');
    }

    public function down(): void {}
};
