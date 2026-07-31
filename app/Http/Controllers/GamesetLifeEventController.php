<?php

namespace App\Http\Controllers;

use App\Models\LifeEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GamesetLifeEventController extends Controller
{
    public function index(Request $request)
    {
        $query = LifeEvent::query();

        if ($request->filled('chapter')) {
            $query->where('chapter', $request->chapter);
        }
        if ($request->filled('asset_category')) {
            $v = $request->asset_category;
            if ($v === 'general') {
                $query->whereNull('asset_category');
            } else {
                $query->where('asset_category', $v);
            }
        }
        if ($request->filled('polarity')) {
            $query->where('is_positive', $request->polarity === 'positive');
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($x) => $x->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"));
        }

        $events = $query->orderByDesc('is_active')->orderBy('chapter')->orderBy('title')->get();

        $stats = [
            'total'    => LifeEvent::count(),
            'active'   => LifeEvent::where('is_active', true)->count(),
            'positive' => LifeEvent::where('is_positive', true)->count(),
            'negative' => LifeEvent::where('is_positive', false)->count(),
            'general'  => LifeEvent::whereNull('asset_category')->count(),
            'asset'    => LifeEvent::whereNotNull('asset_category')->count(),
        ];

        return view('gameset.life-events.index', compact('events', 'stats'));
    }

    public function create()
    {
        return view('gameset.life-events.form', ['mode' => 'create', 'event' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateEvent($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['effect_data'] = $this->parseEffectData($request);

        LifeEvent::create($data);

        return redirect()->route('gameset.life-events.index')
            ->with('success', "Life event \"{$data['title']}\" created.");
    }

    public function edit(LifeEvent $lifeEvent)
    {
        return view('gameset.life-events.form', ['mode' => 'edit', 'event' => $lifeEvent]);
    }

    public function update(Request $request, LifeEvent $lifeEvent)
    {
        $data = $this->validateEvent($request, $lifeEvent->id);
        $data['effect_data'] = $this->parseEffectData($request);
        $lifeEvent->update($data);

        return redirect()->route('gameset.life-events.index')
            ->with('success', "Life event \"{$lifeEvent->title}\" updated.");
    }

    public function destroy(LifeEvent $lifeEvent)
    {
        $title = $lifeEvent->title;
        $lifeEvent->delete();

        return redirect()->route('gameset.life-events.index')
            ->with('success', "Life event \"{$title}\" deleted.");
    }

    public function toggleActive(LifeEvent $lifeEvent): JsonResponse
    {
        $lifeEvent->update(['is_active' => !$lifeEvent->is_active]);
        return response()->json(['is_active' => $lifeEvent->is_active]);
    }

    private function validateEvent(Request $request, ?int $excludeId = null): array
    {
        $slugRule = $excludeId
            ? "nullable|string|max:100|unique:life_events,slug,{$excludeId}"
            : 'nullable|string|max:100|unique:life_events,slug';

        return $request->validate([
            'title'            => 'required|string|max:120',
            'slug'             => $slugRule,
            'chapter'          => 'required|string|in:8-12,13-17,18-25,26+,all',
            'asset_category'   => 'nullable|string|in:vehicle,property,business,investment,gadget',
            'description'      => 'nullable|string',
            'flavor_text'      => 'nullable|string',
            'educational_note' => 'nullable|string',
            'effect_type'      => 'required|string|in:balance_delta,market_event,credit_adjust,bill_assign,career_change',
            'probability'      => 'required|numeric|min:0|max:1',
            'icon'             => 'nullable|string|max:10',
            'is_positive'      => 'boolean',
            'is_active'        => 'boolean',
        ]);
    }

    /** Build effect_data array from structured form fields. */
    private function parseEffectData(Request $request): array
    {
        return match($request->effect_type) {
            'balance_delta' => [
                'balance_min' => (int) $request->input('ed_balance_min', 0),
                'balance_max' => (int) $request->input('ed_balance_max', 0),
            ],
            'market_event' => [
                'market_categories' => [[
                    'category' => $request->input('ed_market_category', 'investment'),
                    'pct'      => (float) $request->input('ed_market_pct', 0),
                ]],
            ],
            'credit_adjust' => [
                'credit_min' => (int) $request->input('ed_credit_min', 0),
                'credit_max' => (int) $request->input('ed_credit_max', 0),
            ],
            'bill_assign' => [
                'bill_slug' => $request->input('ed_bill_slug', ''),
            ],
            'career_change' => [
                'income_delta_min' => (int) $request->input('ed_income_min', 0),
                'income_delta_max' => (int) $request->input('ed_income_max', 0),
            ],
            default => [],
        };
    }
}
