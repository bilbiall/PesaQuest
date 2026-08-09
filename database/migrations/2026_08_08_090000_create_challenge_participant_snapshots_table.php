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
        Schema::create('challenge_participant_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank')->nullable();
            $table->decimal('progress', 12, 2)->default(0);
            $table->date('snapshot_date');
            $table->timestamps();
            $table->unique(['challenge_participant_id', 'snapshot_date'], 'chal_part_snap_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_participant_snapshots');
    }
};
