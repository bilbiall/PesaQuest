<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->string('added_by_name', 100)->nullable();
            $table->timestamps();
            $table->unique(['school_subscription_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_members');
    }
};
