<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamas', function (Blueprint $table) {
            // Null = use the game-wide default rate (Chama::DEFAULT_LOAN_INTEREST_RATE).
            $table->decimal('loan_interest_rate', 5, 2)->nullable()->after('pool_balance');
            // Loan interest + (later) other profit collected into pool_balance that
            // hasn't been declared as a dividend yet — kept separate from
            // pool_balance so a dividend never accidentally pays out members'
            // own contributed principal.
            $table->decimal('undistributed_gains', 12, 2)->default(0)->after('loan_interest_rate');
        });

        Schema::create('chama_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chama_id')->constrained()->cascadeOnDelete();
            $table->foreignId('borrower_member_id')->constrained('chama_members')->cascadeOnDelete();
            $table->decimal('principal', 12, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('outstanding_balance', 12, 2);
            $table->decimal('payment_amount', 12, 2);
            $table->unsignedInteger('payment_period_ticks')->default(30);
            $table->unsignedInteger('disbursed_at_tick');
            $table->unsignedInteger('due_at_tick');
            $table->unsignedInteger('next_payment_tick');
            $table->unsignedInteger('payments_made')->default(0);
            $table->unsignedInteger('payments_missed')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('chama_dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chama_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('chama_members')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('choice', 10)->nullable(); // 'cash' | 'reinvest', null = pending
            $table->timestamp('declared_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chama_dividends');
        Schema::dropIfExists('chama_loans');

        Schema::table('chamas', function (Blueprint $table) {
            $table->dropColumn(['loan_interest_rate', 'undistributed_gains']);
        });
    }
};
