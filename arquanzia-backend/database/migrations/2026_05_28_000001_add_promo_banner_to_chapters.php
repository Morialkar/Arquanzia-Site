<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->boolean('promo_banner_enabled')->default(false)->after('published_at');
            $table->string('promo_banner_text', 500)->nullable()->after('promo_banner_enabled');
            $table->string('promo_banner_button_label', 100)->nullable()->after('promo_banner_text');
            $table->string('promo_banner_button_url', 500)->nullable()->after('promo_banner_button_label');
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn(['promo_banner_enabled', 'promo_banner_text', 'promo_banner_button_label', 'promo_banner_button_url']);
        });
    }
};
