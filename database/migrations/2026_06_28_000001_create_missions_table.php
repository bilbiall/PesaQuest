<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon', 10)->default('🎯');
            $table->string('district_slug');
            $table->unsignedInteger('sequence_order')->default(1);
            $table->json('requirements');  // {type, value, ...}
            $table->json('rewards');       // {xp, kes, badge_slug}
            $table->string('badge_slug')->nullable();
            $table->string('age_group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
