<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->unsignedInteger('value'); // percent (1-100) or KES amount
            $table->unsignedInteger('max_redemptions')->nullable(); // null = unlimited
            $table->unsignedInteger('redemptions_count')->default(0);
            $table->foreignId('plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete(); // null = any plan
            $table->timestamp('expires_at')->nullable(); // null = never expires
            $table->boolean('is_active')->default(true); // admin pause switch
            $table->string('note', 150)->nullable(); // admin memo, e.g. "Launch promo"
            $table->timestamps();
        });

        Schema::table('mpesa_transactions', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('amount')->constrained('coupons')->nullOnDelete();
            $table->unsignedInteger('discount_kes')->default(0)->after('coupon_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('coupon_code', 30)->nullable()->after('amount_paid');
            $table->unsignedInteger('discount_kes')->default(0)->after('coupon_code');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['coupon_code', 'discount_kes']);
        });
        Schema::table('mpesa_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('discount_kes');
        });
        Schema::dropIfExists('coupons');
    }
};
