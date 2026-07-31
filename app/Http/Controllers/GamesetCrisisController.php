<?php

namespace App\Http\Controllers;

use App\Models\FinancialCrisis;
use Illuminate\Http\Request;

/**
 * Financial Crisis management for gameset users (same table the admin panel
 * uses, so both surfaces see the same schedule).
 */
class GamesetCrisisController extends Controller
{
    /** Ready-made crisis recipes shown as one-click templates in the UI. */
    public const PRESETS = [
        ['name' => 'NSE Market Crash',       'icon' => '📉', 'effect_type' => 'investment_drop', 'effect_amount' => 25, 'description' => 'Foreign investors pull out of the Nairobi Securities Exchange. Pending investment deals lose a quarter of their value overnight.'],
        ['name' => 'Property Market Slump',  'icon' => '🏚️', 'effect_type' => 'asset_drop',      'effect_amount' => 15, 'description' => 'Oversupply and high interest rates cool the property and asset market. Owned assets shed value.'],
        ['name' => 'Drought Food Inflation', 'icon' => '🌾', 'effect_type' => 'balance_drain',   'effect_amount' => 10, 'description' => 'A failed rain season pushes food prices up sharply. Everyday costs eat into every wallet.'],
        ['name' => 'Fuel Price Shock',       'icon' => '⛽', 'effect_type' => 'balance_drain',   'effect_amount' => 5,  'description' => 'Global oil prices spike and EPRA passes it on at the pump. Transport and goods cost more.'],
        ['name' => 'Economic Recession',     'icon' => '✂️', 'effect_type' => 'salary_cut',      'effect_amount' => 20, 'description' => 'Companies restructure to survive a slowdown. Salaries are cut while the recession lasts.'],
        ['name' => 'Currency Devaluation',   'icon' => '💱', 'effect_type' => 'asset_drop',      'effect_amount' => 10, 'description' => 'The shilling weakens against the dollar. Imported goods and asset values take a hit.'],
    ];

    public function index()
    {
        $crises = FinancialCrisis::orderByDesc('active_from')->take(50)->get();

        return view('gameset.crises.index', [
            'crises'      => $crises,
            'presets'     => self::PRESETS,
            'effectTypes' => FinancialCrisis::EFFECT_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:120',
            'icon'          => 'nullable|string|max:10',
            'description'   => 'required|string|max:500',
            'effect_type'   => 'required|in:investment_drop,asset_drop,balance_drain,salary_cut',
            'effect_amount' => 'required|numeric|min:0.5|max:90',
            'warning_at'    => 'required|date',
            'active_from'   => 'required|date|after:warning_at',
            'active_until'  => 'required|date|after:active_from',
        ]);

        $data['icon']          = $data['icon'] ?: '⚠️';
        $data['is_percentage'] = true;
        $data['created_by']    = auth()->id();

        $crisis = FinancialCrisis::create($data);

        return response()->json(['success' => true, 'crisis' => $crisis]);
    }

    public function destroy(FinancialCrisis $crisis)
    {
        $crisis->delete();
        return response()->json(['success' => true]);
    }
}
