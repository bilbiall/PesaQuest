<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GamesetQuestController extends Controller
{
    public function index(Request $request)
    {
        $query       = Quest::query();
        $hasLevelCol = Schema::hasColumn('quests', 'level_required');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($x) => $x->where('title', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"));
        }
        if ($hasLevelCol && $request->filled('level')) {
            $query->where('level_required', $request->level);
        }
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->active);
        }
        // Auto-drafted approval queue (factory, blueprint or mixer line), not yet published
        if ($request->boolean('drafts') && Schema::hasColumn('quests', 'source')) {
            $query->whereIn('source', ['factory', 'blueprint', 'mixer'])->where('is_active', false);
        }
        // Age-group tabs: a specific group also sees the 'all'/untargeted quests
        // it would get in-game; the special 'all' tab shows ONLY untargeted ones.
        if ($request->filled('age')) {
            $age = $request->age;
            $query->where(function ($q) use ($age) {
                $age === 'all'
                    ? $q->where('age_group', 'all')->orWhereNull('age_group')
                    : $q->where('age_group', $age);
            });
        }

        $query->orderBy($hasLevelCol ? 'level_required' : 'sort_order')->orderBy('sort_order');
        $quests = $query->get();

        $hasTriggers = Schema::hasColumn('quests', 'triggers');
        $stats = [
            'total'    => Quest::count(),
            'active'   => Quest::where('is_active', true)->count(),
            'triggered'=> $hasTriggers
                ? Quest::where(fn($q) => $q->whereNotNull('triggers')->orWhereNotNull('trigger_type'))->count()
                : Quest::whereNotNull('trigger_type')->count(),
            'drafts'   => \App\Services\QuestFactory::pendingDrafts(),
        ];

        return view('gameset.quests.index', compact('quests', 'stats'));
    }

    public function create()
    {
        return view('gameset.quests.form', ['mode' => 'create', 'quest' => null]);
    }

    /**
     * Generated Quests hub — every quest the machines wrote (factory,
     * blueprint sweep, mixer), drafts and published, with instant client-side
     * filtering and bulk publish/discard.
     */
    public function generated()
    {
        $hasSource = Schema::hasColumn('quests', 'source');
        $quests = $hasSource
            ? Quest::whereIn('source', ['factory', 'blueprint', 'mixer'])
                ->orderBy('is_active')->orderBy('level_required')->orderBy('id')
                ->get()
            : collect();

        // Similarity families: quests sharing the same trigger-type combo are
        // "near-twins" (same recipe at different levels/ages/values). The page
        // badges them so redundant generations are easy to spot and prune.
        $quests->each(function ($q) {
            $types = collect($q->triggers ?? [])->pluck('type')->filter()->all();
            if (empty($types) && $q->trigger_type) $types = [$q->trigger_type];
            sort($types);
            $q->combo_sig = implode('+', $types) ?: 'none';
        });
        $familySizes = $quests->groupBy('combo_sig')->map->count();
        $quests->each(fn ($q) => $q->family_count = (int) ($familySizes[$q->combo_sig] ?? 1));

        $stats = [
            'total'     => $quests->count(),
            'drafts'    => $quests->where('is_active', false)->count(),
            'published' => $quests->where('is_active', true)->count(),
            'factory'   => $quests->where('source', 'factory')->count(),
            'blueprint' => $quests->where('source', 'blueprint')->count(),
            'mixer'     => $quests->where('source', 'mixer')->count(),
        ];

        return view('gameset.quests.generated', compact('quests', 'stats'));
    }

    /** Bulk publish (activate) a set of generated quests. */
    public function bulkActivate(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer|exists:quests,id']);
        $n = Quest::whereIn('id', $data['ids'])->where('is_active', false)->update(['is_active' => true]);
        return response()->json(['activated' => $n]);
    }

    /** Bulk discard a set of generated quests (drafts or otherwise). */
    public function bulkDelete(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer|exists:quests,id']);
        $quests = Quest::whereIn('id', $data['ids'])->get();
        foreach ($quests as $q) {
            if ($q->image) Storage::disk('public')->delete($q->image);
            $q->delete();
        }
        return response()->json(['deleted' => $quests->count()]);
    }

    /** Persist a drag-and-drop ordering from the quests index. */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:quests,id',
        ]);

        foreach ($data['ids'] as $i => $id) {
            Quest::where('id', $id)->update(['sort_order' => $i]);
        }

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $data = $this->validateAndBuildQuest($request);
        Quest::create($data);

        return redirect()->route('gameset.quests.index')
            ->with('success', "Quest \"{$data['title']}\" created.");
    }

    public function edit(Quest $quest)
    {
        return view('gameset.quests.form', ['mode' => 'edit', 'quest' => $quest]);
    }

    public function update(Request $request, Quest $quest)
    {
        $data = $this->validateAndBuildQuest($request, $quest);
        $quest->update($data);

        return redirect()->route('gameset.quests.index')
            ->with('success', "Quest \"{$quest->title}\" updated.");
    }

    public function destroy(Quest $quest)
    {
        $title = $quest->title;
        if ($quest->image) {
            Storage::disk('public')->delete($quest->image);
        }
        $quest->delete();

        return redirect()->route('gameset.quests.index')
            ->with('success', "Quest \"{$title}\" deleted.");
    }

    public function toggleActive(Quest $quest): JsonResponse
    {
        $quest->update(['is_active' => !$quest->is_active]);
        return response()->json(['is_active' => $quest->is_active]);
    }

    /**
     * GET /gameset/quests/trigger-options?type=buy_item_category
     * Returns selectable value options for the given trigger type.
     */
    public function triggerOptions(Request $request): JsonResponse
    {
        $type = $request->get('type', '');
        $opts = match ($type) {
            'buy_item_category' => DB::table('assets')
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->map(fn($c) => ['value' => $c, 'label' => ucfirst($c)])
                ->values(),

            'buy_item_slug' => DB::table('assets')
                ->where('is_active', true)
                ->select('slug', 'name', 'category')
                ->orderBy('category')->orderBy('name')
                ->get()
                ->map(fn($a) => ['value' => $a->slug, 'label' => $a->name, 'group' => ucfirst($a->category)])
                ->values(),

            'take_course' => DB::table('city_courses')
                ->where('is_active', true)
                ->select('slug', 'title')
                ->orderBy('title')
                ->get()
                ->map(fn($c) => ['value' => $c->slug, 'label' => $c->title])
                ->values(),

            'get_job' => DB::table('city_jobs')
                ->where('is_active', true)
                ->select('id', 'title', 'employer_name')
                ->orderBy('title')
                ->get()
                ->map(fn($j) => ['value' => (string)$j->id, 'label' => "{$j->title} @ {$j->employer_name}"])
                ->values(),

            'earn_badge' => DB::table('badges')
                ->select('slug', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($b) => ['value' => $b->slug, 'label' => $b->name])
                ->values(),

            'reach_level' => collect(range(1, 10))
                ->map(fn($i) => ['value' => (string)$i, 'label' => "Level {$i}"])
                ->values(),

            // Chain link: completing another quest (grouped by required level)
            'complete_quest' => Quest::orderBy(Schema::hasColumn('quests', 'level_required') ? 'level_required' : 'sort_order')
                ->orderBy('title')
                ->get(['id', 'title', 'level_required'])
                ->map(fn($q) => ['value' => (string)$q->id, 'label' => $q->title, 'group' => 'Level ' . ($q->level_required ?? 1)])
                ->values(),

            // Numeric threshold types — no pre-set options, client shows number input
            'reach_balance', 'reach_savings', 'reach_net_worth', 'deposit_savings'
                => collect([]),

            // No-value types
            default => collect([]),
        };

        return response()->json(['options' => $opts, 'is_numeric' => $this->_isNumericTrigger($type)]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function validateAndBuildQuest(Request $request, ?Quest $existing = null): array
    {
        $validFieldKeys = array_column(\App\Services\CareerService::fields(), 'key');

        $data = $request->validate([
            'title'                 => 'required|string|max:120',
            'description'           => 'nullable|string|max:500',
            'instructions'          => 'nullable|string',
            'lesson'                => 'nullable|string|max:600',
            'icon'                  => 'nullable|string|max:10',
            'image'                 => 'nullable|image|max:5120', // 5MB max raw, we compress
            'xp_reward'             => 'required|integer|min:0|max:99999',
            'kes_reward'            => 'required|integer|min:0|max:9999999',
            'level_required'        => 'required|integer|min:1|max:20',
            'sort_order'            => 'nullable|integer|min:0',
            'age_group'             => 'required|string|in:8-12,13-17,18-25,26+,all',
            'career_fields_all'     => 'nullable|boolean',
            'career_fields'         => 'nullable|array',
            'career_fields.*'       => 'string|in:' . implode(',', $validFieldKeys),
            'is_active'             => 'boolean',
            'triggers'              => 'nullable|string', // JSON-encoded from form
            'trigger_mode'          => 'nullable|in:all,any',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['icon']      = $data['icon'] ?: '📜';

        // "All career paths" wins over any leftover checkbox selection
        $data['career_fields'] = $request->boolean('career_fields_all')
            ? null
            : (!empty($data['career_fields']) ? array_values($data['career_fields']) : null);
        unset($data['career_fields_all']);

        // Decode and store multi-trigger JSON
        $triggersJson = $request->input('triggers', '[]');
        $triggers     = json_decode($triggersJson, true) ?? [];
        // Filter out empty trigger rows
        $triggers = array_values(array_filter($triggers, fn($t) => !empty($t['type'])));
        $data['triggers']     = !empty($triggers) ? $triggers : null;
        $data['trigger_mode'] = $request->input('trigger_mode', 'all');

        // Also mirror first trigger to legacy columns for backward compat
        if (!empty($triggers)) {
            $first = $triggers[0];
            $data['trigger_type']  = $first['type'];
            $data['trigger_value'] = implode(',', $first['values'] ?? []) ?: null;
            $data['trigger_label'] = $first['label'] ?? null;
        } else {
            $data['trigger_type']  = null;
            $data['trigger_value'] = null;
            $data['trigger_label'] = null;
        }

        // Handle image upload/removal
        unset($data['image']);
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $this->_processQuestImage($request->file('image'), $existing?->image);
            if ($imagePath) $data['image'] = $imagePath;
        } elseif ($request->input('_remove_image') === '1' && $existing?->image) {
            Storage::disk('public')->delete($existing->image);
            $data['image'] = null;
        }

        return $data;
    }

    private function _processQuestImage($file, ?string $oldPath): ?string
    {
        // Delete old image if replacing
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $mime = $file->getMimeType();

        // SVG: store directly (no compression needed — scalable)
        if ($mime === 'image/svg+xml') {
            return $file->store('quest-images', 'public');
        }

        // Raster: load with GD, resize to max 400×400, save as JPEG 82%
        $raw = file_get_contents($file->getPathname());
        $src = @imagecreatefromstring($raw);
        if (!$src) return $file->store('quest-images', 'public'); // fallback

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $max  = 400;

        if ($srcW > $max || $srcH > $max) {
            $ratio  = min($max / $srcW, $max / $srcH);
            $dstW   = max(1, (int) round($srcW * $ratio));
            $dstH   = max(1, (int) round($srcH * $ratio));
            $dst    = imagecreatetruecolor($dstW, $dstH);
            // Preserve transparency for PNG
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
            imagedestroy($src);
            $src = $dst;
        }

        $dir  = storage_path('app/public/quest-images');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $name = Str::uuid() . '.jpg';
        $dest = $dir . '/' . $name;
        imagejpeg($src, $dest, 82);
        imagedestroy($src);

        return 'quest-images/' . $name;
    }

    private function _isNumericTrigger(string $type): bool
    {
        return in_array($type, ['reach_balance', 'reach_savings', 'reach_net_worth', 'deposit_savings']);
    }
}
