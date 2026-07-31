<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A simple, no-interest gift between friends — distinct from the
     * structured friend_loans negotiation. Money moves instantly; the row
     * is a receipt/history log and lets us enforce a daily send count
     * against balance-pumping abuse.
     */
    public function up(): void
    {
        Schema::create('friend_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('message', 120)->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'created_at']);
            $table->index(['recipient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friend_gifts');
    }
};
