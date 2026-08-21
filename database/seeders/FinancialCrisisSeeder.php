<?php

namespace Database\Seeders;

use App\Models\FinancialCrisis;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * FinancialCrisisSeeder — the crisis engine (CrisisService) has been fully
 * wired for a while (warnings, effects, timeline entries, salary-cut windows)
 * but zero FinancialCrisis rows had ever existed — a built feature nobody
 * ever scheduled. Crises are global, wall-clock-scheduled broadcasts (not
 * per-level catalog content), so this seeds a rolling queue of 6 staggered
 * events over the next ~6 weeks from whenever this runs, covering all 4
 * effect types.
 *
 * Uses firstOrCreate keyed by name — safe to re-run. Deliberately does NOT
 * use updateOrCreate: once a crisis is scheduled (or has already fired),
 * re-running db:seed must never push its dates into the future again.
 */
class FinancialCrisisSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('financial_crises')) {
            $this->command->warn('FinancialCrisisSeeder: financial_crises table not found — skipping.');
            return;
        }

        // created_by is a required column (admin who scheduled it) — attribute
        // these seeded crises to the first admin account, falling back to the
        // first user at all so this never hard-fails on a fresh database.
        $creatorId = \App\Models\User::where('is_admin', true)->value('id')
            ?? \App\Models\User::min('id');

        if (!$creatorId) {
            $this->command->warn('FinancialCrisisSeeder: no users exist yet — skipping.');
            return;
        }

        $crises = [
            [
                'name'            => 'Nairobi Stock Exchange Correction',
                'description'     => 'A broad NSE sell-off wipes value off pending investments city-wide.',
                'icon'            => '📉',
                'effect_type'     => 'investment_drop',
                'effect_amount'   => 15,
                'is_percentage'   => true,
                'days_until'      => 3,
                'duration_days'   => 4,
            ],
            [
                'name'            => 'Shilling Depreciation Shock',
                'description'     => 'The Kenyan shilling weakens sharply against major currencies overnight, eating into cash savings.',
                'icon'            => '💱',
                'effect_type'     => 'balance_drain',
                'effect_amount'   => 8,
                'is_percentage'   => true,
                'days_until'      => 10,
                'duration_days'   => 3,
            ],
            [
                'name'            => 'Property Market Slowdown',
                'description'     => 'A glut of unsold units and tighter mortgage lending cools property values across the city.',
                'icon'            => '🏚️',
                'effect_type'     => 'asset_drop',
                'effect_amount'   => 12,
                'is_percentage'   => true,
                'days_until'      => 18,
                'duration_days'   => 5,
            ],
            [
                'name'            => 'Recession Layoffs Wave',
                'description'     => 'A sharp economic slowdown forces employers city-wide to cut pay to avoid layoffs.',
                'icon'            => '📉',
                'effect_type'     => 'salary_cut',
                'effect_amount'   => 20,
                'is_percentage'   => true,
                'days_until'      => 26,
                'duration_days'   => 7,
            ],
            [
                'name'            => 'Fuel Price Spike',
                'description'     => 'A sudden global oil price spike drives up fuel and transport costs, straining every household budget.',
                'icon'            => '⛽',
                'effect_type'     => 'balance_drain',
                'effect_amount'   => 800,
                'is_percentage'   => false,
                'days_until'      => 34,
                'duration_days'   => 3,
            ],
            [
                'name'            => 'Global Market Jitters',
                'description'     => 'Volatility in global markets spills over into local pending investments.',
                'icon'            => '🌍',
                'effect_type'     => 'investment_drop',
                'effect_amount'   => 10,
                'is_percentage'   => true,
                'days_until'      => 42,
                'duration_days'   => 4,
            ],
        ];

        $created = 0;

        foreach ($crises as $c) {
            $activeFrom  = now()->addDays($c['days_until']);
            $activeUntil = $activeFrom->copy()->addDays($c['duration_days']);
            $warningAt   = $activeFrom->copy()->subDays(2);

            $crisis = FinancialCrisis::firstOrCreate(
                ['name' => $c['name']],
                [
                    'description'   => $c['description'],
                    'icon'          => $c['icon'],
                    'effect_type'   => $c['effect_type'],
                    'effect_amount' => $c['effect_amount'],
                    'is_percentage' => $c['is_percentage'],
                    'warning_at'    => $warningAt,
                    'active_from'   => $activeFrom,
                    'active_until'  => $activeUntil,
                    'is_processed'  => false,
                    'created_by'    => $creatorId,
                ]
            );

            if ($crisis->wasRecentlyCreated) $created++;
        }

        $this->command->info("FinancialCrisisSeeder: {$created} crisis event(s) newly scheduled (" . count($crises) . ' total in the queue).');
    }
}
