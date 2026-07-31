<?php

namespace App\Http\Controllers;

use App\Models\InvestmentDeal;
use Illuminate\Http\Request;

class GamesetDealController extends Controller
{
    public function index(Request $request)
    {
        $query = InvestmentDeal::query();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($x) => $x->where('title', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"));
        }
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->active);
        }

        $deals = $query->orderBy('sort_order')->orderBy('risk_level')->get();

        $stats = [
            'total'  => InvestmentDeal::count(),
            'active' => InvestmentDeal::where('is_active', true)->count(),
        ];

        return view('gameset.deals.index', compact('deals', 'stats'));
    }

    public function create()
    {
        return view('gameset.deals.form', ['deal' => null, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validate($request);
        $data['created_by'] = auth()->id();
        InvestmentDeal::create($data);

        return redirect()->route('gameset.deals.index')
            ->with('success', "Deal \"{$data['title']}\" created.");
    }

    public function edit(InvestmentDeal $deal)
    {
        return view('gameset.deals.form', ['deal' => $deal, 'mode' => 'edit']);
    }

    public function update(Request $request, InvestmentDeal $deal)
    {
        $data = $this->validate($request);
        $deal->update($data);

        return redirect()->route('gameset.deals.index')
            ->with('success', "Deal \"{$deal->title}\" updated.");
    }

    public function destroy(InvestmentDeal $deal)
    {
        $title = $deal->title;
        $deal->delete();
        return redirect()->route('gameset.deals.index')
            ->with('success', "Deal \"{$title}\" deleted.");
    }

    public function toggleActive(InvestmentDeal $deal)
    {
        $deal->update(['is_active' => !$deal->is_active]);
        return response()->json(['is_active' => $deal->is_active]);
    }

    private function validate(Request $request): array
    {
        $data = $request->validate([
            'title'                => 'required|string|max:120',
            'description'          => 'nullable|string|max:500',
            'category'             => 'required|string|max:40',
            'icon'                 => 'nullable|string|max:8',
            'cost'                 => 'required|integer|min:100',
            'min_return_pct'       => 'required|numeric|min:0|max:500',
            'max_return_pct'       => 'required|numeric|min:0|max:500',
            'loss_pct'             => 'required|numeric|min:0|max:100',
            'success_probability'  => 'required|numeric|min:0.01|max:0.99',
            'maturity_ticks'       => 'required|integer|min:1|max:365',
            'risk_level'           => 'required|integer|min:1|max:5',
            'lesson'               => 'nullable|string|max:600',
            'sort_order'           => 'nullable|integer|min:0',
            'is_active'            => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['icon']      = $data['icon'] ?: '💼';
        return $data;
    }
}
