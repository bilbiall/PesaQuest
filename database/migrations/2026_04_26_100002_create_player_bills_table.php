<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('frequency_ticks');
            $table->unsignedBigInteger('next_due_tick');
            $table->unsignedBigInteger('last_paid_tick')->nullable();
            $table->enum('status', ['active', 'overdue', 'suspended', 'cancelled'])->default('active');
            $table->unsignedSmallInteger('missed_count')->default(0);
            $table->unsignedBigInteger('overdue_since_tick')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_bills');
    }
};
