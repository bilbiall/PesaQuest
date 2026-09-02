<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\MmfSponsor;
use Illuminate\Database\Seeder;

/**
 * Placeholder MMF sponsors + their sponsored fund listings — demonstrates
 * the multi-provider feature end to end (Admin → Sponsors, GameSet Hub →
 * Assets → Product Type, Marketplace card branding) before any real sponsor
 * deal is signed. Deliberately FICTIONAL company names, not real Kenyan
 * financial institutions — attaching a real company's name/logo here would
 * read as an actual endorsement that doesn't exist yet. Swap these for real
 * sponsor names/rates/logos via Admin → Sponsors once a deal is confirmed;
 * everything downstream (Marketplace branding, comparison grouping) already
 * works off whatever's actually in these two tables.
 */
class MmfSponsorSeeder extends Seeder
{
    public function run(): void
    {
        $sponsors = [
            [
                'name'     => 'Horizon Trust',
                'tagline'  => 'Grow your money, steadily.',
                'min_rate' => 9.0, // % p.a. — daily rate fluctuates inside this band, edit in GameSet Hub once real
                'max_rate' => 13.0,
            ],
            [
                'name'     => 'Nexa Capital',
                'tagline'  => 'Smart investing, made simple.',
                'min_rate' => 8.5,
                'max_rate' => 12.0,
            ],
        ];

        foreach ($sponsors as $s) {
            $sponsor = MmfSponsor::firstOrCreate(
                ['name' => $s['name']],
                ['tagline' => $s['tagline'], 'is_active' => true]
            );

            Asset::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($s['name']) . '-mmf'],
                [
                    'name'                => "{$s['name']} Money Market Fund",
                    'brand'               => $s['name'],
                    'category'            => 'fixed_income',
                    'product_type'        => 'money_market_fund',
                    'tier'                => 1,
                    'age_group'           => 'all',
                    'base_price'          => 1000, // suggested first deposit — actual amount is player-chosen
                    'monthly_income'      => 0,    // unused: MMF compounds daily via LifeSimulator::settleMmfInterest()
                    'monthly_cost'        => 0,
                    'income_description'  => "{$s['min_rate']}%–{$s['max_rate']}% p.a. (net of fund fees), compounding daily",
                    'cost_description'    => '',
                    'appreciation_rate'   => 0,
                    'volatility'          => 0,
                    'risk_level'          => 1,
                    'icon'                => '💰',
                    'description'         => "A Money Market Fund run by {$s['name']}. Pools your cash with thousands of others and lends it out safely, paying you daily interest — invest any amount, top up anytime, withdraw whenever you like.",
                    'flavor_text'         => $s['tagline'],
                    'educational_note'    => 'Money Market Funds beat a plain bank savings account while staying easy to access — no lock-in, unlike a T-Bill or T-Bond. The rate moves daily, so different providers pay slightly different (and slightly variable) returns — comparing before you commit is always worth it.',
                    'creates_bill_slug'   => null,
                    'max_per_player'      => 1,
                    'is_active'           => true,
                    'badge'               => 'new',
                    'mmf_sponsor_id'      => $sponsor->id,
                    'mmf_min_rate'        => $s['min_rate'],
                    'mmf_max_rate'        => $s['max_rate'],
                    'rate_updated_at'     => now(),
                ]
            );
        }

        $this->command?->info('MMF Sponsors: seeded ' . count($sponsors) . ' placeholder provider(s) and their funds.');
    }
}
