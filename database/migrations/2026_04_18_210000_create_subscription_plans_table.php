<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('months');
            $table->unsignedInteger('price_kes');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default plans
        DB::table('subscription_plans')->insert([
            ['key' => 'monthly',   'name' => 'Monthly',   'months' => 1,  'price_kes' => 299,  'is_active' => 1, 'is_featured' => 0, 'description' => 'Full access for 1 month',   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'quarterly', 'name' => 'Quarterly', 'months' => 3,  'price_kes' => 799,  'is_active' => 1, 'is_featured' => 0, 'description' => 'Full access for 3 months',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'biannual',  'name' => 'Biannual',  'months' => 6,  'price_kes' => 1499, 'is_active' => 1, 'is_featured' => 1, 'description' => 'Full access for 6 months',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'annual',    'name' => 'Annual',    'months' => 12, 'price_kes' => 2499, 'is_active' => 1, 'is_featured' => 0, 'description' => 'Full access for 12 months', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
