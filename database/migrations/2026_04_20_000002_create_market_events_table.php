<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('market_events', function (Blueprint $table) {
            $table->id();
            $table->string('age_group', 10)->nullable()->index();
            $table->string('title');
            $table->string('description');
            $table->enum('effect_type', ['bonus', 'penalty'])->default('bonus');
            $table->unsignedInteger('effect_amount');
            $table->string('icon', 10)->default('📢');
            $table->unsignedTinyInteger('probability')->default(25);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_market_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('market_events')->cascadeOnDelete();
            $table->date('triggered_date');
            $table->timestamps();
            $table->unique(['user_id', 'event_id', 'triggered_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_market_events');
        Schema::dropIfExists('market_events');
    }
};
