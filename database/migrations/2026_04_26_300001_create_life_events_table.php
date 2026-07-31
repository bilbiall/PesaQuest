<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('life_events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('chapter', ['student','graduate','hustler','settler','builder','elder','all'])->default('all');
            $table->string('title');
            $table->text('description');
            $table->text('flavor_text');
            $table->text('educational_note');
            $table->enum('effect_type', ['balance_delta','market_event','credit_delta','compound','narrative'])->default('narrative');
            $table->json('effect_data')->nullable();
            $table->decimal('probability', 4, 3)->default(0.010);
            $table->string('icon', 8)->default('⚡');
            $table->boolean('is_positive')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('life_events');
    }
};
