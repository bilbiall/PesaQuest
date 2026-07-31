<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('npcs')) return;
        Schema::create('npcs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('nickname', 60)->nullable();
            $table->string('role', 50); // friend, boss, parent, landlord, investor, colleague
            $table->string('avatar_url', 500)->nullable();
            $table->string('cover_color', 20)->default('#6366f1');
            $table->text('description')->nullable();
            $table->string('personality', 255)->nullable();
            $table->unsignedTinyInteger('initial_relationship')->default(50); // 0–100
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('gameset_id')->nullable(); // loose ref, no FK constraint
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npcs');
    }
};
