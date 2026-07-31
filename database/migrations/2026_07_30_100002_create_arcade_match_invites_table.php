<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A direct invite from a Rivals Trail creator to a specific friend —
     * not a shareable token/link like ChamaInvite (that solves invite-by-URL
     * to a possibly-unknown user). Here the invitee is always a known,
     * already-accepted friend, so a plain pivot keyed by invited_user_id
     * lets the lobby query "rounds invited to me" directly.
     */
    public function up(): void
    {
        Schema::create('arcade_match_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arcade_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invited_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();
            $table->unique(['arcade_match_id', 'invited_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arcade_match_invites');
    }
};
