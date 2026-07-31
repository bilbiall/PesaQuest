<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand')->nullable();
            $table->enum('category', ['vehicle', 'property', 'business', 'investment', 'gadget']);
            $table->unsignedTinyInteger('tier')->default(1);
            $table->enum('age_group', ['8-12', '13-17', '18-25', '26+', 'all'])->default('all');
            $table->unsignedInteger('base_price');
            $table->integer('monthly_income')->default(0);
            $table->integer('monthly_cost')->default(0);
            $table->string('income_description')->nullable();
            $table->string('cost_description')->nullable();
            $table->decimal('appreciation_rate', 5, 2)->default(0);
            $table->decimal('volatility', 3, 2)->default(0);
            $table->unsignedTinyInteger('risk_level')->default(2);
            $table->string('icon', 8)->default('📦');
            $table->text('description');
            $table->text('flavor_text');
            $table->text('educational_note');
            $table->string('creates_bill_slug')->nullable();
            $table->unsignedTinyInteger('max_per_player')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
