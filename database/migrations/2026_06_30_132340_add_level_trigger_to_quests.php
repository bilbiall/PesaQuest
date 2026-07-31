<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->unsignedTinyInteger('level_required')->default(1)->after('sort_order');
            $table->string('trigger_type', 60)->nullable()->after('level_required');
            // e.g. 'buy_item_category', 'buy_item', 'earn_balance', 'invest', 'complete_scenario'
            $table->string('trigger_value', 120)->nullable()->after('trigger_type');
            // e.g. 'mobile_device', '5000', 'any'
            $table->string('trigger_label')->nullable()->after('trigger_value');
            // Human-readable: "Buy any mobile device in Marketplace"
            $table->unsignedInteger('kes_reward')->default(0)->after('xp_reward');
        });

        // Migrate existing sort_order values to level_required
        DB::statement('UPDATE quests SET level_required = GREATEST(1, sort_order) WHERE sort_order > 0');

        // Parse existing [TRIGGER:xxx] from instructions into dedicated columns
        $quests = DB::table('quests')->whereNotNull('instructions')->get();
        foreach ($quests as $quest) {
            if (preg_match('/\[TRIGGER:([^\]]+)\]/', $quest->instructions ?? '', $m)) {
                $raw = trim($m[1]);
                // raw might be "buy_item_category:mobile_device" or just "buy_item_category"
                if (str_contains($raw, ':')) {
                    [$type, $value] = explode(':', $raw, 2);
                } else {
                    $type = $raw;
                    $value = 'any';
                }
                DB::table('quests')->where('id', $quest->id)->update([
                    'trigger_type'  => trim($type),
                    'trigger_value' => trim($value),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn(['level_required', 'trigger_type', 'trigger_value', 'trigger_label', 'kes_reward']);
        });
    }
};
