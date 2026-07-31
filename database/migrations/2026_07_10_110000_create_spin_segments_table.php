<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spin_segments', function (Blueprint $table) {
            $table->id();
            $table->string('label', 40);
            $table->string('emoji', 10)->default('💰');
            $table->string('color', 12)->default('#6366f1');
            // balance = add/subtract KES (negative = fine), credit = credit-score
            // delta, xp = points, salary_2x = double next salary, badge = reserved
            $table->string('type', 20)->default('balance');
            $table->integer('value')->default(0);
            $table->unsignedSmallInteger('weight')->default(10);
            $table->string('tier', 10)->default('good'); // good | great | bad
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_segments');
    }
};
