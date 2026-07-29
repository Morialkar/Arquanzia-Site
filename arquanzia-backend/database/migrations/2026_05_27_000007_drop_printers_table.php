<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('printers');
    }

    public function down(): void
    {
        // Intentionally empty — this table is permanently removed
    }
};
