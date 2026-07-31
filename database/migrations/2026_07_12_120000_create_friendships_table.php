<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Friends system: requests flow requester → addressee and must be
     * accepted before any social feature (P2P loans, chama invites) opens
     * up. Players find each other by display name or a short friend code
     * (users.friend_code, generated lazily on first visit).
     */
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('addressee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['requester_id', 'addressee_id']);
            $table->index(['addressee_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('friend_code', 10)->nullable()->unique()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('friend_code');
        });
    }
};
