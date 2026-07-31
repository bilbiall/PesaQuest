<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use Illuminate\Http\Request;

class GamesetLoanController extends Controller
{
    public function index(Request $request)
    {
        $products = LoanProduct::orderBy('sort_order')->orderBy('min_credit_score')->get();

        $stats = [
            'total'  => LoanProduct::count(),
            'active' => LoanProduct::where('is_active', true)->count(),
        ];

        return view('gameset.loans.index', compact('products', 'stats'));
    }

    public function create()
    {
        return view('gameset.loans.form', ['product' => null, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);
        $data['created_by'] = auth()->id();
        LoanProduct::create($data);

        return redirect()->route('gameset.loans.index')
            ->with('success', "Loan product \"{$data['name']}\" created.");
    }

    public function edit(LoanProduct $loan)
    {
        return view('gameset.loans.form', ['product' => $loan, 'mode' => 'edit']);
    }

    public function update(Request $request, LoanProduct $loan)
    {
        $data = $this->validateProduct($request);
        $loan->update($data);

        return redirect()->route('gameset.loans.index')
            ->with('success', "Loan product \"{$loan->name}\" updated.");
    }

    public function destroy(LoanProduct $loan)
    {
        $name = $loan->name;
        $loan->delete();
        return redirect()->route('gameset.loans.index')
            ->with('success', "Loan product \"{$name}\" deleted.");
    }

    public function toggleActive(LoanProduct $loan)
    {
        $loan->update(['is_active' => !$loan->is_active]);
        return response()->json(['is_active' => $loan->is_active]);
    }

    private function validateProduct(Request $request): array
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:120',
            'icon'                 => 'nullable|string|max:8',
            'description'          => 'nullable|string|max:500',
            'min_amount'           => 'required|integer|min:100',
            'max_amount'           => 'required|integer|min:100',
            'annual_interest_rate' => 'required|numeric|min:1|max:200',
            'term_ticks'           => 'required|integer|min:7|max:3650',
            'payment_period_ticks' => 'required|integer|min:1|max:90',
            'min_credit_score'     => 'required|integer|min:300|max:850',
            'sort_order'           => 'nullable|integer|min:0',
            'is_active'            => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['icon']      = $data['icon'] ?: '🏦';
        return $data;
    }
}
