<?php

namespace App\Http\Controllers;

use App\Models\Dream;
use Illuminate\Http\Request;

class GamesetDreamController extends Controller
{
    public function index(Request $request)
    {
        $query = Dream::query()->orderBy('sort_order')->orderBy('price');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('tagline', 'like', "%{$q}%"));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $dreams = $query->get();

        $stats = [
            'total'    => Dream::count(),
            'active'   => Dream::where('is_active', true)->count(),
            'owned'    => \App\Models\PlayerDream::count(),
            'avg_price'=> (int) Dream::avg('price'),
        ];

        return view('gameset.dreams.index', compact('dreams', 'stats'));
    }

    public function create()
    {
        return view('gameset.dreams.form', ['mode' => 'create', 'dream' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateDream($request);
        $data['slug'] = \Illuminate\Support\Str::slug($data['name']) . '-' . uniqid();
        Dream::create($data);

        return redirect()->route('gameset.dreams.index')->with('success', "Dream \"{$data['name']}\" created.");
    }

    public function edit(Dream $dream)
    {
        return view('gameset.dreams.form', ['mode' => 'edit', 'dream' => $dream]);
    }

    public function update(Request $request, Dream $dream)
    {
        $data = $this->validateDream($request);
        $dream->update($data);

        return redirect()->route('gameset.dreams.index')->with('success', "Dream \"{$dream->name}\" updated.");
    }

    public function destroy(Dream $dream)
    {
        $name = $dream->name;
        $dream->delete();

        return redirect()->route('gameset.dreams.index')->with('success', "Dream \"{$name}\" deleted.");
    }

    public function toggleActive(Dream $dream)
    {
        $dream->update(['is_active' => !$dream->is_active]);
        return response()->json(['is_active' => $dream->is_active]);
    }

    private function validateDream(Request $request): array
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'tagline'     => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
            'icon'        => 'nullable|string|max:10',
            'image_url'   => 'nullable|string|max:500',
            'price'       => 'required|integer|min:1',
            'category'    => 'required|in:property,vehicle,travel,legacy,business,lifestyle',
            'min_level'   => 'nullable|integer|min:1|max:99',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $data['icon']       = $data['icon'] ?: '🌟';
        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
