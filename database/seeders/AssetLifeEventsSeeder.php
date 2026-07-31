<?php

namespace Database\Seeders;

use App\Models\LifeEvent;
use Illuminate\Database\Seeder;

/**
 * Seeds two classes of life events:
 * 1. Asset-triggered events (asset_category set) — only fire if player owns that type
 * 2. Social pressure events (harambee, family obligations) — fire for all chapters
 */
class AssetLifeEventsSeeder extends Seeder
{
    public function run(): void
    {
        $events = [

            // ══════════════════════════════════════════════════════════════
            // VEHICLE EVENTS
            // ══════════════════════════════════════════════════════════════
            [
                'slug'             => 'boda-rider-quit',
                'chapter'          => 'all',
                'asset_category'   => 'vehicle',
                'title'            => 'Your Bodaboda Rider Quit',
                'description'      => 'Kamau gave you one day\'s notice — he found a better deal. Your boda sat idle for weeks.',
                'flavor_text'      => '"Siwezi kuendelea. Ninaenda Nakuru." He was gone by Friday.',
                'educational_note' => 'Income-generating assets need reliable operators. Always have a backup rider or build a contract with clear terms.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -12000, 'balance_max' => -5000],
                'probability'      => 0.018,
                'icon'             => '🏍',
                'is_positive'      => false,
                'is_active'        => true,
            ],
            [
                'slug'             => 'boda-accident-repair',
                'chapter'          => 'all',
                'asset_category'   => 'vehicle',
                'title'            => 'Vehicle Accident — Repair Needed',
                'description'      => 'Your vehicle was involved in a minor accident. No injuries, but the engine needs work.',
                'flavor_text'      => 'The mechanic at Grogan Road quoted Ksh 18,000. "Spare parts zimepanda."',
                'educational_note' => 'Vehicle insurance is not optional — it pays for repairs and protects your income stream. NHIF covers people, not machines.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -22000, 'balance_max' => -8000],
                'probability'      => 0.012,
                'icon'             => '💥',
                'is_positive'      => false,
                'is_active'        => true,
            ],
            [
                'slug'             => 'vehicle-great-month',
                'chapter'          => 'all',
                'asset_category'   => 'vehicle',
                'title'            => 'Bumper Month for Your Vehicle',
                'description'      => 'Transport demand spiked — concert season, school reopening, and a fuel price dip. Your vehicle earned extra.',
                'flavor_text'      => '"Rider wako alifanya vizuri sana." The daily reports were pleasing.',
                'educational_note' => 'Physical assets can surprise you upside — but don\'t count on it. Let surplus months build your maintenance reserve.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => 4000, 'balance_max' => 12000],
                'probability'      => 0.015,
                'icon'             => '🏆',
                'is_positive'      => true,
                'is_active'        => true,
            ],

            // ══════════════════════════════════════════════════════════════
            // PROPERTY EVENTS
            // ══════════════════════════════════════════════════════════════
            [
                'slug'             => 'tenant-missed-rent',
                'chapter'          => 'all',
                'asset_category'   => 'property',
                'title'            => 'Tenant Missed Rent This Month',
                'description'      => 'Your tenant hasn\'t paid. They claim a "family emergency" but you\'ve heard that before.',
                'flavor_text'      => '"Nitakulipa mwezi ujao. Nisamehe." The message came at midnight.',
                'educational_note' => 'Screen tenants carefully and have a written lease with penalty clauses. A good tenant is your biggest asset as a landlord.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -10000, 'balance_max' => -6000],
                'probability'      => 0.020,
                'icon'             => '🏠',
                'is_positive'      => false,
                'is_active'        => true,
            ],
            [
                'slug'             => 'tenant-paid-early',
                'chapter'          => 'all',
                'asset_category'   => 'property',
                'title'            => 'Tenant Paid Two Months Early',
                'description'      => 'Your tenant is planning a trip and paid ahead. You have two months of rent in hand.',
                'flavor_text'      => 'A dream tenant. Don\'t lose this one.',
                'educational_note' => 'Good tenant relationships are worth investing in — sometimes small gestures (fixing that leaking tap) keep your best tenants for years.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => 8000, 'balance_max' => 18000],
                'probability'      => 0.010,
                'icon'             => '🎉',
                'is_positive'      => true,
                'is_active'        => true,
            ],
            [
                'slug'             => 'property-plumbing-burst',
                'chapter'          => 'all',
                'asset_category'   => 'property',
                'title'            => 'Plumbing Emergency at Your Rental',
                'description'      => 'A burst pipe flooded the ground floor. Repairs are urgent — tenants are threatening to leave.',
                'flavor_text'      => '"Fundi wa maji anasema itatumia wiki moja na nusu." Expensive week ahead.',
                'educational_note' => 'Always set aside 5-10% of rental income for maintenance reserves. Emergencies will happen — the question is whether you\'re ready.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -25000, 'balance_max' => -8000],
                'probability'      => 0.012,
                'icon'             => '💧',
                'is_positive'      => false,
                'is_active'        => true,
            ],

            // ══════════════════════════════════════════════════════════════
            // BUSINESS EVENTS
            // ══════════════════════════════════════════════════════════════
            [
                'slug'             => 'business-competitor-arrived',
                'chapter'          => 'all',
                'asset_category'   => 'business',
                'title'            => 'New Competitor Next Door',
                'description'      => 'A well-funded competitor opened right next to your business. Your sales dropped by 25% this month.',
                'flavor_text'      => 'They have a generator, a big sign, and free delivery. Customers are curious.',
                'educational_note' => 'Competition is normal. Respond by strengthening your unique value — customer relationships, quality, or specialisation. Price wars only hurt you.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -8000, 'balance_max' => -3000],
                'probability'      => 0.015,
                'icon'             => '🏪',
                'is_positive'      => false,
                'is_active'        => true,
            ],
            [
                'slug'             => 'business-viral-moment',
                'chapter'          => 'all',
                'asset_category'   => 'business',
                'title'            => 'Your Business Went Viral on TikTok',
                'description'      => 'A customer posted about your business and it blew up. Queues formed. You ran out of stock.',
                'flavor_text'      => '"Hii ni noma sana." Even M-Pesa was pinging all night.',
                'educational_note' => 'Viral moments are temporary — but they are an opportunity to convert visitors into loyal customers. Follow up with quality and consistency.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => 8000, 'balance_max' => 30000],
                'probability'      => 0.008,
                'icon'             => '📱',
                'is_positive'      => true,
                'is_active'        => true,
            ],
            [
                'slug'             => 'business-stock-theft',
                'chapter'          => 'all',
                'asset_category'   => 'business',
                'title'            => 'Stock Theft at Your Business',
                'description'      => 'You discovered missing stock — worth about Ksh 15,000. An inside job or a careless night.',
                'flavor_text'      => 'The security camera was conveniently "broken" that evening.',
                'educational_note' => 'Internal controls matter as much as sales. Stocktaking, locks, and trusted staff reduce losses. Insurance for business stock is worth its cost.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -20000, 'balance_max' => -5000],
                'probability'      => 0.012,
                'icon'             => '🚨',
                'is_positive'      => false,
                'is_active'        => true,
            ],

            // ══════════════════════════════════════════════════════════════
            // INVESTMENT EVENTS
            // ══════════════════════════════════════════════════════════════
            [
                'slug'             => 'market-rally',
                'chapter'          => 'all',
                'asset_category'   => 'investment',
                'title'            => 'Market Rally — NSE Up 12%',
                'description'      => 'Strong earnings reports and foreign investor interest lifted the NSE. Your portfolio grew.',
                'flavor_text'      => '"NSE imepanda sana." Business Daily front page.',
                'educational_note' => 'Market rallies are not guarantees of future returns. Use them to review your portfolio, not to take on more risk.',
                'effect_type'      => 'market_event',
                'effect_data'      => [
                    'market_categories' => [
                        ['category' => 'investment', 'pct' => 0.09],
                    ],
                ],
                'probability'      => 0.012,
                'icon'             => '📈',
                'is_positive'      => true,
                'is_active'        => true,
            ],
            [
                'slug'             => 'market-correction',
                'chapter'          => 'all',
                'asset_category'   => 'investment',
                'title'            => 'Market Correction — Investments Dropped',
                'description'      => 'Global headwinds hit the NSE. Your portfolio dropped in value — on paper, at least.',
                'flavor_text'      => '"Hii recession inatuathiri sisi wote." The financial pages are red.',
                'educational_note' => 'Paper losses are not real losses until you sell. Long-term investors who stayed the course during past corrections recovered and gained more.',
                'effect_type'      => 'market_event',
                'effect_data'      => [
                    'market_categories' => [
                        ['category' => 'investment', 'pct' => -0.08],
                    ],
                ],
                'probability'      => 0.015,
                'icon'             => '📉',
                'is_positive'      => false,
                'is_active'        => true,
            ],

            // ══════════════════════════════════════════════════════════════
            // SOCIAL PRESSURE EVENTS (harambee / family obligations)
            // ══════════════════════════════════════════════════════════════
            [
                'slug'             => 'cousin-harambee',
                'chapter'          => 'all',
                'asset_category'   => null,
                'title'            => "Cousin's Harambee — Everyone is Chipping In",
                'description'      => 'Your cousin is unwell and the family organised a harambee. Your contribution was expected.',
                'flavor_text'      => '"Familia inakusubiri." Uncle called three times.',
                'educational_note' => 'Social contributions are part of Kenyan culture — but they should come from budgeted money, not emergency funds. Plan for social obligations monthly.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -3000, 'balance_max' => -1000],
                'probability'      => 0.018,
                'icon'             => '🤝',
                'is_positive'      => false,
                'is_active'        => true,
            ],
            [
                'slug'             => 'school-fees-request',
                'chapter'          => 'all',
                'asset_category'   => null,
                'title'            => "Family School Fees Help",
                'description'      => 'Your younger sibling\'s school fees came up short. Mum asked if you could help cover the balance.',
                'flavor_text'      => '"Wewe ni mkubwa sasa. Msaada kidogo." How do you say no?',
                'educational_note' => 'Family financial support is a reality for many Kenyans. Budget for it as a fixed monthly obligation — it reduces guilt and builds family capital.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -8000, 'balance_max' => -3000],
                'probability'      => 0.015,
                'icon'             => '🎒',
                'is_positive'      => false,
                'is_active'        => true,
            ],
            [
                'slug'             => 'colleague-funeral-collection',
                'chapter'          => 'all',
                'asset_category'   => null,
                'title'            => 'Office Collection — Colleague Funeral',
                'description'      => 'A colleague lost a parent. The office took up a collection. You contributed.',
                'flavor_text'      => 'These things happen. Community support matters.',
                'educational_note' => 'Life insurance and funeral cover protect families from financial catastrophe during grief. Even a small policy makes a difference.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -2000, 'balance_max' => -500],
                'probability'      => 0.020,
                'icon'             => '🕊️',
                'is_positive'      => false,
                'is_active'        => true,
            ],
            [
                'slug'             => 'wedding-contribution',
                'chapter'          => 'all',
                'asset_category'   => null,
                'title'            => "Friend's Wedding — You Were on the Guest List",
                'description'      => 'An old friend is getting married. The M-Pesa request came with "minimum Ksh 2,000".',
                'flavor_text'      => '"Si lazima utoe mingi — lakini tafadhali." Complicated.',
                'educational_note' => 'Social expenses compound fast. Track them monthly and set a social budget — otherwise every celebration chips away at your goals.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => -5000, 'balance_max' => -1500],
                'probability'      => 0.012,
                'icon'             => '💒',
                'is_positive'      => false,
                'is_active'        => true,
            ],
            [
                'slug'             => 'unexpected-windfall-uncle',
                'chapter'          => 'all',
                'asset_category'   => null,
                'title'            => 'Uncle Sent You Something From Abroad',
                'description'      => 'Your uncle in the diaspora sent you some money. No occasion, just a thoughtful transfer.',
                'flavor_text'      => '"Kijana, kaa hivi hivi." Three words and Ksh from the UK.',
                'educational_note' => 'Windfalls should be treated as savings, not spending. Invest at least 50% before touching the rest.',
                'effect_type'      => 'balance_delta',
                'effect_data'      => ['balance_min' => 5000, 'balance_max' => 20000],
                'probability'      => 0.008,
                'icon'             => '✈️',
                'is_positive'      => true,
                'is_active'        => true,
            ],
        ];

        foreach ($events as $event) {
            // Merge with defaults for fields not set (asset_category included)
            LifeEvent::updateOrCreate(
                ['slug' => $event['slug']],
                $event
            );
        }
    }
}
