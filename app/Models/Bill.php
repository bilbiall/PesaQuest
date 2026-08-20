<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'age_group', 'name', 'slug', 'description', 'flavor_text', 'consequence_text',
        'amount', 'net_worth_tiers', 'frequency_ticks', 'category', 'icon',
        'is_essential', 'credit_impact_pay', 'credit_impact_miss',
        'auto_assign', 'trigger', 'min_chapter', 'is_active',
    ];

    protected $casts = [
        'is_essential'    => 'boolean',
        'auto_assign'     => 'boolean',
        'is_active'       => 'boolean',
        'net_worth_tiers' => 'array',
    ];

    public function playerBills()
    {
        return $this->hasMany(PlayerBill::class);
    }

    /** Human label for the billing cycle. */
    public function frequencyLabel(): string
    {
        return match(true) {
            $this->frequency_ticks <= 7  => 'Weekly',
            $this->frequency_ticks <= 14 => 'Fortnightly',
            $this->frequency_ticks <= 30 => 'Monthly',
            $this->frequency_ticks <= 90 => 'Termly',
            default                      => 'Annually',
        };
    }

    /** The amount this bill should charge for a player at the given net
     *  worth — the highest tier whose min_net_worth the player has crossed,
     *  or the flat `amount` if no tiers are configured. Tiers are locked in
     *  once at assignment time (see LifeSimulator::assignEligibleBills) —
     *  an already-assigned PlayerBill never re-prices mid-cycle. */
    public function resolveAmount(int $netWorth): int
    {
        $tiers = $this->net_worth_tiers['tiers'] ?? [];
        if (empty($tiers)) {
            return (int) $this->amount;
        }

        $amount = (int) $this->amount;
        $best   = -1;

        foreach ($tiers as $tier) {
            $min = (int) ($tier['min_net_worth'] ?? 0);
            if ($netWorth >= $min && $min >= $best) {
                $best   = $min;
                $amount = (int) ($tier['amount'] ?? $amount);
            }
        }

        return $amount;
    }
}
