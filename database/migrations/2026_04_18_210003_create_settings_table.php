<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general | smtp | mpesa
            $table->timestamps();
        });

        // Seed default empty settings
        $now = now();
        DB::table('settings')->insert([
            // SMTP
            ['key' => 'smtp_host',       'value' => '',          'group' => 'smtp', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'smtp_port',       'value' => '587',       'group' => 'smtp', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'smtp_username',   'value' => '',          'group' => 'smtp', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'smtp_password',   'value' => '',          'group' => 'smtp', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'smtp_encryption', 'value' => 'tls',       'group' => 'smtp', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'smtp_from_email', 'value' => '',          'group' => 'smtp', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'smtp_from_name',  'value' => 'PesaQuest', 'group' => 'smtp', 'created_at' => $now, 'updated_at' => $now],
            // M-Pesa / Daraja
            ['key' => 'mpesa_env',             'value' => 'sandbox', 'group' => 'mpesa', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mpesa_consumer_key',    'value' => '',        'group' => 'mpesa', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mpesa_consumer_secret', 'value' => '',        'group' => 'mpesa', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mpesa_shortcode',       'value' => '174379',  'group' => 'mpesa', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mpesa_passkey',         'value' => '',        'group' => 'mpesa', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mpesa_account_ref',     'value' => 'PesaQuest', 'group' => 'mpesa', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
