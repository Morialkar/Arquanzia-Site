<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('reader_font', 20)->default('standard')->after('theme_pref');
            $table->unsignedTinyInteger('reader_font_size')->default(18)->after('reader_font');
        });

        Schema::table('delivery_emails', function (Blueprint $table) {
            $table->boolean('use_reader_preferences')->default(false)->after('format');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_emails', function (Blueprint $table) {
            $table->dropColumn('use_reader_preferences');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reader_font', 'reader_font_size']);
        });
    }
};
