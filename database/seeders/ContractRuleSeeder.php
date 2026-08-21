<?php

namespace Database\Seeders;

use App\Models\ContractRule;
use Illuminate\Database\Seeder;

/**
 * The only 4 ContractRule rows in production (created ad-hoc via the GameSet
 * Automation admin UI, never seeded) already use 'any N of M' for the two
 * early bands (L1-2, L3-5) — that part is fine. The L6-9 and L10-99 bands
 * are still 'all' (must clear every objective), the exact fairness problem
 * flagged in an earlier brainstorm ("2/3 or 3/5 feels fairer"). Contracts
 * self-tune difficulty from the player's own state (bills, jobs, chama,
 * mood — see ContractService::eligibleArchetypes()), so unlike Quests this
 * system doesn't need per-age_group bands; age-aware flavor already comes
 * from PesaVoice's band-aware copy. Scope here is just fixing the two
 * unfair bands in place, not inventing a new age x level matrix.
 */
class ContractRuleSeeder extends Seeder
{
    public function run(): void
    {
        // L6-9: was 'all' 4 of 4-5 objectives -> 'any' 3 of 4-5
        ContractRule::updateOrCreate(
            ['age_group' => 'all', 'level_min' => 6, 'level_max' => 9],
            [
                'objectives_min'   => 4,
                'objectives_max'   => 5,
                'completion_mode'  => 'any',
                'required_count'   => 3,
                'duration_days'    => 7,
                'active_contracts' => 2,
                'reward_xp'        => 380,
                'reward_kes'       => 500,
                'is_active'        => true,
            ]
        );

        // L10-99: was 'all' 5 of 5 objectives -> 'any' 4 of 5
        ContractRule::updateOrCreate(
            ['age_group' => 'all', 'level_min' => 10, 'level_max' => 99],
            [
                'objectives_min'   => 5,
                'objectives_max'   => 5,
                'completion_mode'  => 'any',
                'required_count'   => 4,
                'duration_days'    => 7,
                'active_contracts' => 2,
                'reward_xp'        => 550,
                'reward_kes'       => 750,
                'is_active'        => true,
            ]
        );
    }
}
