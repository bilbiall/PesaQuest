<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('plan')->constrained('subscription_plans')->nullOnDelete();
            $table->string('payment_method')->default('manual')->after('payment_reference'); // manual | mpesa
            $table->unsignedInteger('amount_paid')->nullable()->after('payment_method');
            $table->string('mpesa_checkout_request_id')->nullable()->after('amount_paid');
            $table->string('mpesa_receipt')->nullable()->after('mpesa_checkout_request_id');
            $table->unsignedBigInteger('approved_by')->nullable()->after('mpesa_receipt');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'payment_method', 'amount_paid', 'mpesa_checkout_request_id', 'mpesa_receipt', 'approved_by']);
        });
    }
};
