<?php

namespace App\Http\Controllers;

use App\Models\Share;
use Illuminate\Http\Request;

class GamesetShareController extends Controller
{
    public function index()
    {
        $shares = Share::orderBy('sort_order')->orderBy('name')->get();

        $stats = [
            'total'  => Share::count(),
            'active' => Share::where('is_active', true)->count(),
        ];

        return view('gameset.shares.index', compact('shares', 'stats'));
    }

    public function create()
    {
        return view('gameset.shares.form', ['share' => null, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validateShare($request);

        $data['previous_price'] = $data['current_price'];
        $data['sort_order']     = $data['sort_order'] ?? (Share::max('sort_order') + 1);

        $share = Share::create($data);

        return redirect()->route('gameset.shares.index')
            ->with('success', "Share \"{$share->name}\" ({$share->symbol}) created!");
    }

    public function edit(Share $share)
    {
        return view('gameset.shares.form', ['share' => $share, 'mode' => 'edit']);
    }

    public function update(Request $request, Share $share)
    {
        $data = $this->validateShare($request, $share->id);
        $share->update($data);

        return redirect()->route('gameset.shares.index')
            ->with('success', "Share \"{$share->name}\" updated.");
    }

    public function destroy(Share $share)
    {
        $share->delete();

        return redirect()->route('gameset.shares.index')
            ->with('success', 'Share deleted.');
    }

    public function toggleActive(Share $share)
    {
        $share->update(['is_active' => !$share->is_active]);
        return response()->json(['is_active' => $share->is_active]);
    }

    private function validateShare(Request $request, ?int $excludeId = null): array
    {
        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'symbol'         => 'required|string|max:12|unique:shares,symbol,' . ($excludeId ?? 'NULL') . ',id',
            'icon'           => 'nullable|string|max:8',
            'sector'         => 'nullable|string|max:40',
            'current_price'  => 'required|numeric|min:0.01',
            'min_price'      => 'required|numeric|min:0.01',
            'max_price'      => 'required|numeric|gt:min_price',
            'volatility'     => 'required|numeric|min:0|max:1',
            'drift'          => 'required|numeric|min:-0.05|max:0.05',
            'sort_order'     => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
        ]);

        $data['icon']      = $data['icon'] ?: '📈';
        $data['symbol']    = strtoupper($data['symbol']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
