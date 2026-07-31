<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('choice_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->decimal('return_rate', 5, 2); // percentage
            $table->integer('return_days');
            $table->decimal('return_amount', 12, 2)->nullable();
            $table->timestamp('mature_at');
            $table->timestamp('credited_at')->nullable();
            $table->enum('status', ['pending', 'matured', 'credited'])->default('pending');
            $table->string('label')->nullable(); // e.g. "Bank savings", "Loan to friend"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
