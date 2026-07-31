<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chama_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chama_id')->constrained('chamas')->cascadeOnDelete();
            $table->foreignId('proposer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['buy_asset', 'sell_asset', 'change_contribution', 'remove_member']);
            $table->json('proposal_data');
            $table->string('title', 120);
            $table->enum('status', ['voting', 'passed', 'rejected', 'executed'])->default('voting');
            $table->integer('votes_yes')->default(0);
            $table->integer('votes_no')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chama_proposals');
    }
};
