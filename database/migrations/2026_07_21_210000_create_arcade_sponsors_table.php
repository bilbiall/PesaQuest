<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sponsor branding for arcade reward tiles — a business/monetization
     * concern, so it's managed from the main Admin panel, not GameSet
     * (which stays focused on game-design content). Moski ships as the
     * first (house) sponsor; admins assign it (or future sponsors) to
     * specific reward tiles from Admin → Sponsors.
     */
    public function up(): void
    {
        Schema::create('arcade_sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('logo_path', 255); // path relative to public/, e.g. moski-logo.png
            $table->string('tagline', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('arcade_tiles', function (Blueprint $table) {
            $table->foreignId('arcade_sponsor_id')->nullable()->after('label')->constrained()->nullOnDelete();
        });

        DB::table('arcade_sponsors')->insert([
            'name'       => 'Moski',
            'logo_path'  => 'moski-logo.png',
            'tagline'    => "it's possible",
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('arcade_tiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('arcade_sponsor_id');
        });
        Schema::dropIfExists('arcade_sponsors');
    }
};
