<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users.date_of_birth already exists (2026_04_16_140242) — private, never
        // displayed; used only for birthday gifts and automatic age-group moves.
        // This tracks the last real-world year a birthday gift was awarded.
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('last_birthday_gift_year')->nullable()->after('age_group');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_birthday_gift_year');
        });
    }
};
