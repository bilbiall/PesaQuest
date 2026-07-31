<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // game_notifications — these were already partially applied; guard each table's block
        if (!$this->indexExists('game_notifications', 'gn_user_read_idx')) {
            Schema::table('game_notifications', function (Blueprint $table) {
                $table->index(['user_id', 'is_read'],    'gn_user_read_idx');
                $table->index(['user_id', 'created_at'], 'gn_user_created_idx');
                $table->index(['user_id', 'type'],       'gn_user_type_idx');
            });
        }

        if (!$this->indexExists('investments', 'inv_user_status_idx')) {
            Schema::table('investments', function (Blueprint $table) {
                $table->index(['user_id', 'status'],    'inv_user_status_idx');
                $table->index(['user_id', 'mature_at'], 'inv_user_mature_idx');
            });
        }

        if (!$this->indexExists('player_bills', 'pb_user_status_idx')) {
            Schema::table('player_bills', function (Blueprint $table) {
                $table->index(['user_id', 'status'],        'pb_user_status_idx');
                $table->index(['user_id', 'next_due_tick'], 'pb_user_due_idx');
            });
        }

        if (!$this->indexExists('player_life_events', 'ple_user_tick_idx')) {
            Schema::table('player_life_events', function (Blueprint $table) {
                $table->index(['user_id', 'tick_triggered'], 'ple_user_tick_idx');
            });
        }

        if (!$this->indexExists('spin_results', 'sr_user_created_idx')) {
            Schema::table('spin_results', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'sr_user_created_idx');
            });
        }

        if (!$this->indexExists('nodes', 'nodes_age_start_idx')) {
            Schema::table('nodes', function (Blueprint $table) {
                $table->index(['age_group', 'is_start'], 'nodes_age_start_idx');
            });
        }

        if (!$this->indexExists('user_progress', 'up_points_idx')) {
            Schema::table('user_progress', function (Blueprint $table) {
                $table->index('points_total', 'up_points_idx');
            });
        }

        if (!$this->indexExists('player_assets', 'pa_user_status_created_idx')) {
            Schema::table('player_assets', function (Blueprint $table) {
                $table->index(['user_id', 'status', 'created_at'], 'pa_user_status_created_idx');
            });
        }

        if (!$this->indexExists('stock_price_history', 'sph_asset_tick_idx')) {
            Schema::table('stock_price_history', function (Blueprint $table) {
                $table->index(['player_asset_id', 'tick'], 'sph_asset_tick_idx');
            });
        }

        if (!$this->indexExists('user_daily_challenges', 'udc_user_challenge_date_idx')) {
            Schema::table('user_daily_challenges', function (Blueprint $table) {
                $table->index(['user_id', 'challenge_id', 'date'], 'udc_user_challenge_date_idx');
            });
        }

        if (!$this->indexExists('user_quests', 'uq_user_completed_idx')) {
            Schema::table('user_quests', function (Blueprint $table) {
                $table->index(['user_id', 'completed_at', 'submitted_at'], 'uq_user_completed_idx');
            });
        }

        if (Schema::hasTable('player_missions') && !$this->indexExists('player_missions', 'pm_user_status_idx')) {
            Schema::table('player_missions', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'pm_user_status_idx');
            });
        }

        if (Schema::hasTable('savings_schemes') && !$this->indexExists('savings_schemes', 'ss_user_archived_idx')) {
            Schema::table('savings_schemes', function (Blueprint $table) {
                $table->index(['user_id', 'is_archived'], 'ss_user_archived_idx');
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'game_notifications'   => ['gn_user_read_idx', 'gn_user_created_idx', 'gn_user_type_idx'],
            'investments'          => ['inv_user_status_idx', 'inv_user_mature_idx'],
            'player_bills'         => ['pb_user_status_idx', 'pb_user_due_idx'],
            'player_life_events'   => ['ple_user_tick_idx'],
            'spin_results'         => ['sr_user_created_idx'],
            'nodes'                => ['nodes_age_start_idx'],
            'user_progress'        => ['up_points_idx'],
            'player_assets'        => ['pa_user_status_created_idx'],
            'stock_price_history'  => ['sph_asset_tick_idx'],
            'user_daily_challenges'=> ['udc_user_challenge_date_idx'],
            'user_quests'          => ['uq_user_completed_idx'],
            'player_missions'      => ['pm_user_status_idx'],
            'savings_schemes'      => ['ss_user_archived_idx'],
        ];

        foreach ($drops as $table => $indexes) {
            if (!Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $t) use ($indexes) {
                foreach ($indexes as $idx) {
                    try { $t->dropIndex($idx); } catch (\Throwable $e) {}
                }
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = \DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
        return count($rows) > 0;
    }
};
