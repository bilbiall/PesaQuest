<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scenario_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('nodes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1=thumbs up, -1=thumbs down
            $table->timestamps();
            $table->unique(['node_id', 'user_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('scenario_ratings'); }
};
