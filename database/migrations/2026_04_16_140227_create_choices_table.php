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
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained()->onDelete('cascade');
            $table->foreignId('next_node_id')->nullable()->constrained('nodes')->onDelete('set null');
            $table->string('label');
            $table->text('description')->nullable();
            $table->integer('points')->default(0);
            $table->integer('sort_order')->default(0);
            $table->json('effect_data')->nullable(); // balance_change, lesson, consequence
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
};
