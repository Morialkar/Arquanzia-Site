<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('webauthn_credentials');
        Schema::dropIfExists('user_identities');
        Schema::dropIfExists('client_magic_links');
        Schema::dropIfExists('bridge_tokens');
        Schema::dropIfExists('personal_tokens');
        Schema::dropIfExists('remember_logins');
    }

    public function down(): void {}
};
