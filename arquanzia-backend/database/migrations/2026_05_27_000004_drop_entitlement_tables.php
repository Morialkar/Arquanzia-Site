<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('entitlement_events');
        Schema::dropIfExists('entitlement_states');
        Schema::dropIfExists('access_controls');
    }

    public function down(): void {}
};
