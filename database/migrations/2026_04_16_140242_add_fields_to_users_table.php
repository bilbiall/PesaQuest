<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('email');
            $table->enum('age_group', ['8-12', '13-17', '18-25', '26+'])->nullable()->after('date_of_birth');
            $table->boolean('is_admin')->default(false)->after('age_group');
            $table->boolean('is_gameset')->default(false)->after('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'age_group', 'is_admin', 'is_gameset']);
        });
    }
};
