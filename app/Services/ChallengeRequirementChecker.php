<?php

namespace App\Services;

use App\Models\PlayerAsset;
use App\Models\PlayerBill;
use App\Models\PlayerLoan;
use App\Models\SavingsScheme;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Optional win restrictions on a Challenge — same JSON-discriminator shape as
 * MissionChecker, so a challenge can demand more than just "highest progress"
 * (e.g. no fair-weather winners who let every bill go overdue).
 */
class ChallengeRequirementChecker
{
    /** @param array<int, array{type: string, value?: int}> $requirements */
    public function passes(User $user, array $requirements): bool
    {
        foreach ($requirements as $req) {
            if (!$this->check($user, $req)) {
                return false;
            }
        }
        return true;
    }

    private function check(User $user, array $req): bool
    {
        return match ($req['type'] ?? '') {
            'bills_paid_all' => !PlayerBill::where('user_id', $user->id)->where('status', 'overdue')->exists(),
            'min_assets'     => PlayerAsset::where('user_id', $user->id)->active()->count() >= (int) ($req['value'] ?? 1),
            'min_savings'    => (Schema::hasTable('savings_schemes')
                ? SavingsScheme::where('user_id', $user->id)->sum('current_amount') : 0) >= (int) ($req['value'] ?? 0),
            'debt_free'      => !PlayerLoan::where('user_id', $user->id)->where('status', 'active')->exists(),
            default          => true,
        };
    }

    /** Human-readable badge text for challenge cards, e.g. "🎯 Must: bills paid, 2+ assets". */
    public function describe(array $requirements): ?string
    {
        if (empty($requirements)) return null;

        $labels = array_map(fn ($req) => match ($req['type'] ?? '') {
            'bills_paid_all' => 'all bills paid',
            'min_assets'     => (int) ($req['value'] ?? 1) . '+ assets',
            'min_savings'    => 'KES ' . number_format((int) ($req['value'] ?? 0)) . '+ saved',
            'debt_free'      => 'debt-free',
            default          => null,
        }, $requirements);

        $labels = array_filter($labels);

        return $labels ? '🎯 Must: ' . implode(', ', $labels) : null;
    }
}
