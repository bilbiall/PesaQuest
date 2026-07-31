<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fun_world_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('icon', 8)->default('🎉');
            $table->string('description', 200)->nullable();
            $table->unsignedInteger('price');
            $table->unsignedTinyInteger('mood_boost_base')->default(10);
            $table->unsignedInteger('xp_reward')->default(15);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fun_world_activities');
    }
};
