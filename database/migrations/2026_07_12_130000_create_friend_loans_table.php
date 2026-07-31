<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * P2P friend loans with structured (no free text) negotiation:
     *   requested → lender offers a rate → borrower accepts, OR counters once
     *   → lender accepts/declines. Activation moves the cash and sets a due
     *   tick on the BORROWER's game clock. Repayment is manual; past the due
     *   tick the borrower's login settles it (garnish what exists, default
     *   the rest with credit damage).
     */
    public function up(): void
    {
        Schema::create('friend_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('borrower_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->unsignedSmallInteger('term_ticks');                 // game days to repay, from activation
            $table->unsignedTinyInteger('rate_pct')->nullable();        // lender's offered rate
            $table->unsignedTinyInteger('counter_rate_pct')->nullable(); // borrower's single counter-offer
            $table->enum('status', ['requested', 'offered', 'countered', 'active', 'declined', 'expired', 'repaid', 'defaulted'])->default('requested');
            $table->unsignedInteger('total_due')->nullable();            // fixed at activation
            $table->unsignedInteger('amount_repaid')->default(0);
            $table->unsignedBigInteger('disbursed_at_tick')->nullable(); // borrower's clock
            $table->unsignedBigInteger('due_at_tick')->nullable();       // borrower's clock
            $table->timestamp('negotiation_expires_at')->nullable();     // unanswered offers rot after 3 real days
            $table->timestamps();

            $table->index(['borrower_id', 'status']);
            $table->index(['lender_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friend_loans');
    }
};
