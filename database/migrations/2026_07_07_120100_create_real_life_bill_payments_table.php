<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A permanent log of real-life bill payments — real_life_bills only ever
 * holds the CURRENT cycle's due date, so without this log "how much did I
 * pay in bills last month" would be unanswerable once a bill rolls forward.
 * bill_name is snapshotted so history survives the bill being edited/deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_life_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('real_life_bill_id')->nullable()->constrained('real_life_bills')->nullOnDelete();
            $table->string('bill_name', 100);
            $table->unsignedInteger('amount');
            $table->date('paid_on');
            $table->timestamps();

            $table->index(['user_id', 'paid_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('real_life_bill_payments');
    }
};
