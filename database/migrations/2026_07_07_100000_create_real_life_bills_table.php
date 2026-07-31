<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Real-life recurring bill reminders — deliberately separate from `bills`/
 * `player_bills`, which are Pesa City game mechanics (credit score, tick-based
 * due dates, auto-assigned by chapter). This table stores a player's OWN
 * real-world bills, on real calendar dates, purely so they can get a push
 * reminder before they're due. Never touches game balance or credit score.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_life_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('icon', 10)->default('🧾');
            $table->string('category', 30)->default('other');
            $table->unsignedInteger('amount')->default(0);
            $table->date('next_due_date');
            $table->boolean('is_recurring')->default(true);
            $table->unsignedInteger('frequency_days')->nullable(); // null = one-off
            $table->unsignedTinyInteger('reminder_lead_days')->default(2);
            $table->timestamp('last_reminded_at')->nullable();
            $table->enum('status', ['active', 'paused', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'next_due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('real_life_bills');
    }
};
