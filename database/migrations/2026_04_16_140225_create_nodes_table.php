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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('scenario_text');
            $table->enum('age_group', ['8-12', '13-17', '18-25', '26+'])->default('8-12');
            $table->enum('type', ['scenario', 'result', 'ending'])->default('scenario');
            $table->boolean('is_start')->default(false);
            $table->boolean('is_free')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('icon')->nullable();
            $table->string('theme_color')->default('#6366f1');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
