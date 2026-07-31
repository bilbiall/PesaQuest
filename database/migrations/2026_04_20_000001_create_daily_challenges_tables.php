<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('age_group', 10)->nullable()->index();
            $table->string('title');
            $table->string('description');
            $table->enum('challenge_type', ['earn_ksh', 'make_decisions', 'save_choices', 'reach_balance'])->default('earn_ksh');
            $table->unsignedInteger('target_value');
            $table->unsignedInteger('xp_bonus')->default(100);
            $table->date('active_date')->nullable()->comment('null = active every day');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_daily_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained('daily_challenges')->cascadeOnDelete();
            $table->date('date')->index();
            $table->unsignedInteger('progress')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'challenge_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_daily_challenges');
        Schema::dropIfExists('daily_challenges');
    }
};
