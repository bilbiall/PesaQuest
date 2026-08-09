<?php

namespace Database\Seeders;

use App\Models\Share;
use Illuminate\Database\Seeder;

class ShareSeeder extends Seeder
{
    public function run(): void
    {
        $shares = [
            ['name' => 'Safaricom PLC',        'symbol' => 'SCOM', 'icon' => '📱', 'sector' => 'Telecoms',      'price' => 18.50,  'volatility' => 0.03, 'drift' => 0.0004],
            ['name' => 'Equity Group',         'symbol' => 'EQTY', 'icon' => '🏦', 'sector' => 'Banking',       'price' => 45.00,  'volatility' => 0.03, 'drift' => 0.0003],
            ['name' => 'KCB Group',            'symbol' => 'KCB',  'icon' => '🏦', 'sector' => 'Banking',       'price' => 32.75,  'volatility' => 0.035,'drift' => 0.0002],
            ['name' => 'East African Breweries','symbol' => 'EABL','icon' => '🍺', 'sector' => 'Manufacturing', 'price' => 165.00, 'volatility' => 0.025,'drift' => 0.0001],
            ['name' => 'British American Tobacco Kenya', 'symbol' => 'BATK', 'icon' => '🚬', 'sector' => 'Manufacturing', 'price' => 380.00, 'volatility' => 0.02, 'drift' => -0.0001],
            ['name' => 'Kenya Airways',        'symbol' => 'KQ',   'icon' => '✈️', 'sector' => 'Aviation',      'price' => 4.20,   'volatility' => 0.08, 'drift' => -0.0005],
            ['name' => 'Bamburi Cement',       'symbol' => 'BAMB', 'icon' => '🧱', 'sector' => 'Manufacturing', 'price' => 38.00,  'volatility' => 0.04, 'drift' => 0.0001],
            ['name' => 'Kenya Power',          'symbol' => 'KPLC', 'icon' => '⚡', 'sector' => 'Energy',        'price' => 2.10,   'volatility' => 0.06, 'drift' => 0.0000],
            ['name' => 'Britam Holdings',      'symbol' => 'BRIT', 'icon' => '🛡️', 'sector' => 'Insurance',     'price' => 6.80,   'volatility' => 0.05, 'drift' => 0.0002],
            ['name' => 'Jubilee Holdings',     'symbol' => 'JUB',  'icon' => '🛡️', 'sector' => 'Insurance',     'price' => 240.00, 'volatility' => 0.03, 'drift' => 0.0002],
            ['name' => 'Co-operative Bank',    'symbol' => 'COOP', 'icon' => '🏦', 'sector' => 'Banking',       'price' => 14.50,  'volatility' => 0.03, 'drift' => 0.0002],
            ['name' => 'Sasini PLC',           'symbol' => 'SASN', 'icon' => '🍃', 'sector' => 'Agriculture',   'price' => 22.00,  'volatility' => 0.05, 'drift' => 0.0001],
            ['name' => 'Kakuzi PLC',           'symbol' => 'KUKZ', 'icon' => '🌱', 'sector' => 'Agriculture',   'price' => 410.00, 'volatility' => 0.04, 'drift' => 0.0002],
            ['name' => 'NCBA Group',           'symbol' => 'NCBA', 'icon' => '🏦', 'sector' => 'Banking',       'price' => 38.50,  'volatility' => 0.03, 'drift' => 0.0002],
        ];

        foreach ($shares as $i => $s) {
            Share::updateOrCreate(['symbol' => $s['symbol']], [
                'name'           => $s['name'],
                'icon'           => $s['icon'],
                'sector'         => $s['sector'],
                'current_price'  => $s['price'],
                'previous_price' => $s['price'],
                'min_price'      => round($s['price'] * 0.35, 2),
                'max_price'      => round($s['price'] * 3.0, 2),
                'volatility'     => $s['volatility'],
                'drift'          => $s['drift'],
                'is_active'      => true,
                'sort_order'     => $i,
            ]);
        }
    }
}
