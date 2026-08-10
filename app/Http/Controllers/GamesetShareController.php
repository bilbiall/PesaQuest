<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\UploadsImages;
use App\Models\Share;
use Illuminate\Http\Request;

class GamesetShareController extends Controller
{
    use UploadsImages;

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
        if ($share->image_url) $this->deleteStoredImage($share->image_url);
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
            'icon'           => 'nullable|string|max:30',
            'image_url'      => 'nullable|string|max:500',
            'image_file'     => 'nullable|image|max:4096',
            'sector'         => 'nullable|string|max:40',
            'current_price'  => 'required|numeric|min:0.01',
            'min_price'      => 'required|numeric|min:0.01',
            'max_price'      => 'required|numeric|gt:min_price',
            'volatility'     => 'required|numeric|min:0|max:1',
            'drift'          => 'required|numeric|min:-0.05|max:0.05',
            'sort_order'     => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
        ]);

        $data['icon']      = $data['icon'] ?: 'trend-up';
        $data['symbol']    = strtoupper($data['symbol']);
        $data['is_active'] = $request->boolean('is_active');
        $data['image_url'] = $this->resolveImage($request, $excludeId ? Share::find($excludeId) : null);
        unset($data['image_file']);

        return $data;
    }

    private function resolveImage(Request $request, ?Share $share): ?string
    {
        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            if ($share?->image_url) $this->deleteStoredImage($share->image_url);
            return '/uploads/' . $this->resizeAndStore($request->file('image_file'), 'shares/logos', 160, 160, 88);
        }

        if ($request->filled('image_url')) {
            return $request->input('image_url');
        }

        return $share?->image_url;
    }
}
