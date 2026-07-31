<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint');
            // MySQL can't index a TEXT column directly — a generated/stored hash gives
            // us a fast unique lookup without truncating the real endpoint.
            $table->string('endpoint_hash', 64);
            $table->string('public_key', 255)->nullable();   // p256dh
            $table->string('auth_token', 255)->nullable();   // auth
            $table->string('content_encoding', 20)->default('aes128gcm');
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedTinyInteger('failed_count')->default(0);
            $table->timestamps();

            $table->unique('endpoint_hash');
            $table->index('user_id');
        });

        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);   // money_alert | achievement | opportunity | announcement | monetization
            $table->string('type', 40);       // maps to GameNotification.type
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_prefs')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_logs');
        Schema::dropIfExists('push_subscriptions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_prefs');
        });
    }
};
