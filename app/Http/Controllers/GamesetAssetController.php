<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GamesetAssetController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search   = $request->get('q', '');

        $query = Asset::orderBy('category')->orderBy('tier')->orderBy('name');

        if ($category !== 'all') {
            $query->where('category', $category);
        }
        if ($search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('brand', 'like', "%{$search}%"));
        }

        $assets = $query->get();

        $stats = [
            'total'    => Asset::count(),
            'active'   => Asset::where('is_active', true)->count(),
            'vehicle'  => Asset::where('category', 'vehicle')->count(),
            'property' => Asset::where('category', 'property')->count(),
            'business' => Asset::where('category', 'business')->count(),
            'investment'=> Asset::where('category', 'investment')->count(),
            'fixed_income' => Asset::where('category', 'fixed_income')->count(),
            'gadget'   => Asset::where('category', 'gadget')->count(),
        ];

        return view('gameset.assets.index', compact('assets', 'category', 'search', 'stats'));
    }

    public function create()
    {
        return view('gameset.assets.form', ['asset' => null, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validateAsset($request);
        $data['slug'] = Str::slug($data['name'] . '-' . uniqid());
        $data['image_url'] = $this->resolveImage($request, null);

        $asset = Asset::create($data);

        return redirect()->route('gameset.assets.edit', $asset)
            ->with('success', "Asset \"{$asset->name}\" created successfully!");
    }

    public function edit(Asset $asset)
    {
        return view('gameset.assets.form', ['asset' => $asset, 'mode' => 'edit']);
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $this->validateAsset($request, $asset->id);
        $data['image_url'] = $this->resolveImage($request, $asset);

        $asset->update($data);

        return redirect()->route('gameset.assets.edit', $asset)
            ->with('success', "Asset updated successfully!");
    }

    public function destroy(Asset $asset)
    {
        // Remove uploaded image if it's a stored file
        if ($asset->image_url && str_contains($asset->image_url, '/storage/assets/')) {
            $path = str_replace('/storage/', 'public/', parse_url($asset->image_url, PHP_URL_PATH));
            Storage::delete($path);
        }
        $asset->delete();

        return redirect()->route('gameset.assets.index')
            ->with('success', "Asset deleted.");
    }

    public function toggleActive(Asset $asset)
    {
        $asset->update(['is_active' => !$asset->is_active]);
        return response()->json(['is_active' => $asset->is_active]);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private function validateAsset(Request $request, ?int $excludeId = null): array
    {
        $data = $request->validate([
            'name'                => 'required|string|max:120',
            'brand'               => 'nullable|string|max:80',
            'category'            => 'required|in:vehicle,property,business,investment,gadget,fixed_income',
            'tier'                => 'required|integer|min:1|max:5',
            'age_group'           => 'required|in:8-12,13-17,18-25,26+,all',
            'icon'                => 'nullable|string|max:8',
            'base_price'          => 'required|integer|min:0',
            'monthly_income'      => 'required|integer|min:0',
            'monthly_cost'        => 'required|integer|min:0',
            'income_period_ticks' => 'required|integer|min:1|max:365',
            'income_description'  => 'nullable|string|max:255',
            'cost_description'    => 'nullable|string|max:255',
            'appreciation_rate'   => 'required|numeric|min:-20|max:20',
            'volatility'          => 'required|numeric|min:0|max:1',
            'risk_level'          => 'required|integer|min:1|max:5',
            'description'         => 'required|string',
            'flavor_text'         => 'required|string|max:200',
            'educational_note'    => 'required|string',
            'creates_bill_slug'   => 'nullable|string|max:80',
            'max_per_player'      => 'required|integer|min:1|max:20',
            'is_active'           => 'boolean',
            'is_luxury'           => 'boolean',
            'badge'               => 'nullable|string|in:popular,trending,new,stable,risky',
            'featured_section'    => 'nullable|string|in:starter_moves,serious_money,high_growth,dividend_builders,lifestyle_upgrades',
            'maturity_ticks'         => 'nullable|integer|min:1|max:3650',
            'locked'                 => 'boolean',
            'early_exit_penalty_pct' => 'required|numeric|min:0|max:100',
            'maturity_bonus_pct'     => 'required|numeric|min:0|max:100',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_luxury'] = $request->boolean('is_luxury');
        $data['locked']    = $request->boolean('locked');
        $data['icon']             = $request->input('icon') ?: null;
        $data['badge']            = $request->input('badge') ?: null;
        $data['featured_section'] = $request->input('featured_section') ?: null;
        $data['maturity_ticks']   = $request->input('maturity_ticks') ?: null;
        return $data;
    }

    private function resolveImage(Request $request, ?Asset $asset): ?string
    {
        // Uploaded file takes priority
        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            // Remove old uploaded file
            if ($asset && $asset->image_url && str_contains($asset->image_url, '/storage/assets/')) {
                $old = 'public/assets/' . basename($asset->image_url);
                Storage::delete($old);
            }
            $file = $request->file('image_file');
            $name = uniqid('asset_') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/assets', $name);
            return '/storage/assets/' . $name;
        }

        // URL provided
        if ($request->filled('image_url')) {
            return $request->input('image_url');
        }

        // Keep existing
        return $asset?->image_url;
    }
}
