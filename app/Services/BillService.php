<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Bill;
use App\Models\PlayerBill;
use App\Models\User;

/**
 * Assigns contextual bills to players when they acquire assets, so that
 * ownership has ongoing costs the player can see and understand.
 *
 * Priority: an explicit `creates_bill_slug` on the asset wins. Otherwise a
 * category-based bill is assigned (vehicle → insurance + fuel, property →
 * service charge) with the amount scaled to the asset's price.
 */
class BillService
{
    /**
     * @return array List of bill names assigned to the player (empty if none).
     */
    public function assignAssetBills(User $user, Asset $asset): array
    {
        $progress = $user->getOrCreateProgress();
        $tick     = $progress->tick_count ?? 0;
        $assigned = [];

        // 1) Explicit designer-configured bill. If the slug points to a template
        //    that doesn't exist, fall through to category bills instead of
        //    silently attaching nothing.
        if ($asset->creates_bill_slug) {
            $template = Bill::where('slug', $asset->creates_bill_slug)->first();
            if ($template) {
                if ($this->attach($user, $template, $template->amount, $tick)) {
                    $assigned[] = $template->name;
                }
                return $assigned;
            }
        }

        // 2) Category-based contextual bills
        foreach ($this->templatesForCategory($asset) as [$template, $amount]) {
            if ($this->attach($user, $template, $amount, $tick)) {
                $assigned[] = $template->name;
            }
        }

        return $assigned;
    }

    /** @return array<int, array{0: Bill, 1: int}> [template, player amount] pairs */
    private function templatesForCategory(Asset $asset): array
    {
        $price = (int) $asset->base_price;

        return match ($asset->category) {
            'vehicle' => [
                [$this->template(
                    'vehicle-insurance',
                    'Vehicle Insurance',
                    'transport',
                    '🛡️',
                    'Comprehensive cover for your vehicle.',
                    'Every car on Kenyan roads needs insurance — it protects you from huge repair or liability costs after an accident.',
                    'Driving uninsured risks fines and paying full accident costs from your pocket.'
                ), max(1500, (int) round($price * 0.015))],
                [$this->template(
                    'vehicle-fuel',
                    'Fuel & Servicing',
                    'transport',
                    '⛽',
                    'Monthly fuel and routine servicing.',
                    'A vehicle is not a one-off cost. Fuel, oil changes and servicing are the real price of ownership.',
                    'Skipping servicing shortens your vehicle\'s life and slashes its resale value.'
                ), max(2000, (int) round($price * 0.008))],
            ],
            'property' => [
                [$this->template(
                    'property-service-charge',
                    'Property Service Charge',
                    'housing',
                    '🏠',
                    'Land rates, security and upkeep for your property.',
                    'Property ownership comes with recurring costs — county land rates, security and maintenance keep your investment healthy.',
                    'Unpaid land rates accrue county penalties and can block a future sale.'
                ), max(1000, (int) round($price * 0.004))],
            ],
            default => [],
        };
    }

    /** Find or create a reusable bill template (amount on template is only a default). */
    private function template(string $slug, string $name, string $category, string $icon, string $description, string $flavor, string $consequence): Bill
    {
        return Bill::firstOrCreate(
            ['slug' => $slug],
            [
                'name'               => $name,
                'age_group'          => 'all',
                'description'        => $description,
                'flavor_text'        => $flavor,
                'consequence_text'   => $consequence,
                'amount'             => 2000,
                'frequency_ticks'    => 30,
                'category'           => $category,
                'icon'               => $icon,
                'is_essential'       => true,
                'credit_impact_pay'  => 5,
                'credit_impact_miss' => -15,
                'auto_assign'        => false,
                'trigger'            => 'asset',
                'is_active'          => true,
            ]
        );
    }

    /** @return bool True if a new PlayerBill was created. */
    private function attach(User $user, Bill $template, int $amount, int $tick): bool
    {
        $alreadyHas = PlayerBill::where('user_id', $user->id)
            ->where('bill_id', $template->id)
            ->whereIn('status', ['active', 'overdue'])
            ->exists();

        if ($alreadyHas) return false;

        PlayerBill::create([
            'user_id'         => $user->id,
            'bill_id'         => $template->id,
            'amount'          => max(1, $amount),
            'frequency_ticks' => $template->frequency_ticks,
            'next_due_tick'   => $tick + $template->frequency_ticks,
            'status'          => 'active',
        ]);

        // Explain WHY this bill now exists — educational context in the bell
        \App\Models\GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'bill_assigned',
            'title'   => "{$template->icon} New Bill: {$template->name}",
            'body'    => 'Ksh ' . number_format(max(1, $amount)) . ' every ' . $template->frequency_ticks . ' game days. '
                       . ($template->flavor_text ?: $template->description),
            'icon'    => $template->icon,
            'data'    => ['bill_id' => $template->id, 'amount' => max(1, $amount)],
        ]);

        return true;
    }
}
