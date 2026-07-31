<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Real, authenticated multi-teacher access to a school subscription —
     * separate from `school_members` (students, who just consume a seat).
     * A school can have several teacher accounts; the first (the buyer) is
     * the `owner` and can invite/remove other teachers.
     */
    public function up(): void
    {
        Schema::create('school_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('role', ['owner', 'teacher'])->default('teacher');
            $table->string('invite_token', 64)->unique();
            $table->enum('status', ['invited', 'active'])->default('invited');
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['school_subscription_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_teachers');
    }
};
