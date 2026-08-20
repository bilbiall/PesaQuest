<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GamesetBillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('age_group')) {
            $query->where('age_group', $request->age_group);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($x) => $x->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"));
        }

        $bills = $query->orderBy('category')->orderBy('amount')->get();

        $stats = [
            'total'    => Bill::count(),
            'active'   => Bill::where('is_active', true)->count(),
            'essential'=> Bill::where('is_essential', true)->count(),
            'housing'  => Bill::where('category', 'housing')->count(),
            'transport'=> Bill::where('category', 'transport')->count(),
            'utilities'=> Bill::where('category', 'utilities')->count(),
            'social'   => Bill::where('category', 'social')->count(),
        ];

        return view('gameset.bills.index', compact('bills', 'stats'));
    }

    public function create()
    {
        return view('gameset.bills.form', ['mode' => 'create', 'bill' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBill($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        Bill::create($data);

        return redirect()->route('gameset.bills.index')
            ->with('success', "Bill \"{$data['name']}\" created successfully.");
    }

    public function edit(Bill $bill)
    {
        return view('gameset.bills.form', ['mode' => 'edit', 'bill' => $bill]);
    }

    public function update(Request $request, Bill $bill)
    {
        $data = $this->validateBill($request, $bill->id);
        $bill->update($data);

        return redirect()->route('gameset.bills.index')
            ->with('success', "Bill \"{$bill->name}\" updated.");
    }

    public function destroy(Bill $bill)
    {
        $name = $bill->name;
        $bill->delete();

        return redirect()->route('gameset.bills.index')
            ->with('success', "Bill \"{$name}\" deleted.");
    }

    public function toggleActive(Bill $bill): JsonResponse
    {
        $bill->update(['is_active' => !$bill->is_active]);
        return response()->json(['is_active' => $bill->is_active]);
    }

    private function validateBill(Request $request, ?int $excludeId = null): array
    {
        $slugRule = $excludeId
            ? "nullable|string|max:80|unique:bills,slug,{$excludeId}"
            : 'nullable|string|max:80|unique:bills,slug';

        // Derived from the live (admin-editable) chapter list, not a hardcoded
        // copy — a stale hardcoded "sage" here used to silently reject the
        // real final chapter key "elder", so min_chapter could never be set
        // to it even though the form offered it.
        $validChapters = array_column(\App\Models\UserProgress::chapters(), 'key');

        $data = $request->validate([
            'name'                          => 'required|string|max:100',
            'slug'                          => $slugRule,
            'description'                   => 'nullable|string',
            'flavor_text'                   => 'nullable|string',
            'consequence_text'              => 'nullable|string',
            'amount'                        => 'required|integer|min:0|max:9999999',
            'net_worth_tiers'                => 'nullable|array',
            'net_worth_tiers.*.min_net_worth'=> 'required_with:net_worth_tiers|integer|min:0',
            'net_worth_tiers.*.amount'       => 'required_with:net_worth_tiers|integer|min:0|max:9999999',
            'frequency_ticks'    => 'required|integer|in:7,14,30,90,182,365',
            'category'           => 'required|string|in:housing,transport,utilities,food,healthcare,education,social,entertainment,tax',
            'icon'               => 'nullable|string|max:10',
            'age_group'          => 'required|string|in:8-12,13-17,18-25,26+,all',
            'min_chapter'        => 'nullable|string|in:' . implode(',', $validChapters),
            'trigger'            => 'nullable|string|in:immediate,chapter,net_worth,asset',
            'is_essential'       => 'boolean',
            'auto_assign'        => 'boolean',
            'credit_impact_pay'  => 'nullable|integer|min:-100|max:100',
            'credit_impact_miss' => 'nullable|integer|min:-100|max:100',
            'is_active'          => 'boolean',
        ]);

        // Reshape into the {"tiers": [...]} structure Bill::resolveAmount()
        // reads — dropping any row a user added then left blank.
        $tiers = collect($data['net_worth_tiers'] ?? [])
            ->filter(fn ($t) => isset($t['min_net_worth'], $t['amount']))
            ->sortBy('min_net_worth')
            ->values()
            ->all();
        $data['net_worth_tiers'] = $tiers ? ['tiers' => $tiers] : null;

        return $data;
    }
}
