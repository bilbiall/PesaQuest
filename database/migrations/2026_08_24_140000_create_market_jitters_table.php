<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bulk-preset, game-time-scheduled share market shocks — the "Market
     * Jitters" system. Unlike Market Watch (a slow-burn hinted news story),
     * a jitter fires and announces in the same pass: a sudden, broad move
     * that no admin needs to trigger by hand. `scheduled_at` is resolved
     * once at seed time from a game-day offset (via GameClock), so the
     * whole roster is spread across game years at whatever speed the clock
     * is currently set to — see database/seeders/MarketJitterSeeder.php.
     */
    public function up(): void
    {
        Schema::create('market_jitters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('lesson')->nullable();
            $table->enum('scope', ['all', 'sector'])->default('all');
            $table->string('sector')->nullable();
            $table->enum('direction', ['up', 'down']);
            $table->decimal('magnitude_pct', 5, 2);
            $table->unsignedSmallInteger('window_steps')->default(8);
            $table->unsignedInteger('game_day_offset');
            $table->timestamp('scheduled_at');
            $table->enum('status', ['scheduled', 'applied'])->default('scheduled');
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('forum_topic_id')->nullable()->constrained('forum_topics')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_jitters');
    }
};
