<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sponsor branding for Marketplace fixed-income products — a
     * business/monetization concern, so (same as arcade_sponsors) it's
     * managed from the main Admin panel, not GameSet. Kept as its own table
     * rather than reusing arcade_sponsors: a sponsor here is a real-world
     * financial institution (e.g. an actual MMF provider) attached to real
     * product terms, a different relationship than a cosmetic board-tile
     * skin, and the two are expected to diverge (rate freshness, disclosure
     * text, a future "learn more" link) in ways a shared table would muddy.
     */
    public function up(): void
    {
        Schema::create('mmf_sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('logo_path', 255)->nullable(); // path relative to public/, e.g. sponsors/britam-logo.png
            $table->string('tagline', 120)->nullable();
            $table->string('website_url', 255)->nullable(); // reserved for a future "learn more" CTA
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mmf_sponsors');
    }
};
