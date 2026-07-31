<?php

namespace App\Http\Controllers;

use App\Models\ContractRule;
use App\Models\PlayerContract;
use App\Models\Quest;
use App\Models\QuestBlueprint;
use App\Models\Setting;
use App\Services\CareerService;
use App\Services\QuestFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * GameSet → Quests → Automation: the "game runs itself" control room.
 * Contract rules (per age group + level band) drive the NPC contract
 * generator; the Quest Factory settings control auto-drafting from
 * new courses/jobs; quest blueprints are the level-ladder printing press.
 */
class GamesetAutomationController extends Controller
{
    public function index()
    {
        $enabled = Schema::hasTable('contract_rules');

        $rules = $enabled
            ? ContractRule::orderBy('age_group')->orderBy('level_min')->get()
            : collect();

        $stats = ['active' => 0, 'completed_week' => 0, 'expired_week' => 0];
        if (Schema::hasTable('player_contracts')) {
            $stats['active']         = PlayerContract::where('status', 'active')->count();
            $stats['completed_week'] = PlayerContract::where('status', 'completed')->where('updated_at', '>=', now()->subDays(7))->count();
            $stats['expired_week']   = PlayerContract::where('status', 'expired')->where('updated_at', '>=', now()->subDays(7))->count();
        }

        $blueprintsEnabled = Schema::hasTable('quest_blueprints');
        $blueprints = $blueprintsEnabled
            ? QuestBlueprint::withCount('quests')->orderBy('level_min')->orderBy('name')->get()
            : collect();

        return view('gameset.automation', [
            'enabled'            => $enabled,
            'rules'              => $rules,
            'stats'              => $stats,
            'npcs'               => config('pesa_voice.npcs', []),
            'archetypes'         => config('pesa_voice.archetypes', []),
            'factoryEnabled'     => Setting::get(QuestFactory::ENABLED_SETTING, '1') === '1',
            'factoryAutopublish' => Setting::get(QuestFactory::AUTOPUBLISH_SETTING, '0') === '1',
            'questGateEnabled'   => Setting::get(\App\Services\QuestGate::SETTING, '1') === '1',
            'pendingDrafts'      => QuestFactory::pendingDrafts(),
            'blueprintsEnabled'  => $blueprintsEnabled,
            'blueprints'         => $blueprints,
            'triggerMeta'        => QuestFactory::TRIGGER_META,
            'careerFields'       => CareerService::fields(),
        ]);
    }

    // ── Quest Blueprints: the level-ladder printing press ──────────────────

    public function storeBlueprint(Request $request)
    {
        QuestBlueprint::create($this->validatedBlueprint($request));
        return back()->with('success', 'Blueprint added — run a sweep (or wait for tonight\'s) to print its quests.');
    }

    public function updateBlueprint(Request $request, QuestBlueprint $blueprint)
    {
        $blueprint->update($this->validatedBlueprint($request));
        return back()->with('success', 'Blueprint updated. Already-printed quests keep their values; new rungs use the new recipe.');
    }

    public function destroyBlueprint(QuestBlueprint $blueprint)
    {
        // Printed quests survive as normal quests; unlink so the ledger stays clean
        Quest::where('blueprint_id', $blueprint->id)->update(['blueprint_id' => null, 'blueprint_slot' => null]);
        $blueprint->delete();
        return back()->with('success', 'Blueprint deleted. Its printed quests remain and are now yours to manage by hand.');
    }

    public function toggleBlueprint(QuestBlueprint $blueprint)
    {
        $blueprint->update(['is_active' => !$blueprint->is_active]);
        return back()->with('success', 'Blueprint ' . ($blueprint->is_active ? 'activated — next sweep prints its missing rungs.' : 'paused — sweeps will skip it.'));
    }

    public function runSweep()
    {
        $s = app(QuestFactory::class)->sweep();
        return back()->with('success', "Sweep finished: {$s['created']} new quest" . ($s['created'] === 1 ? '' : 's') .
            " printed, {$s['existing']} already covered, across {$s['blueprints']} active blueprint" . ($s['blueprints'] === 1 ? '' : 's') . '.');
    }

    // ── Mixers: one-button generation, always to the drafts queue ──────────

    public function mixQuests(Request $request)
    {
        $data = $request->validate([
            'level_min'    => 'required|integer|min:1|max:20',
            'level_max'    => 'required|integer|min:1|max:20|gte:level_min',
            'per_level'    => 'required|integer|min:1|max:12',
            'mix'          => 'required|in:auto,gentle,balanced,spicy',
            'xp_easy'      => 'required|integer|min:1|max:9999',
            'xp_semi'      => 'required|integer|min:1|max:9999',
            'xp_complex'   => 'required|integer|min:1|max:9999',
            'xp_growth'    => 'required|integer|min:0|max:100',
            'age_groups'   => 'nullable|array',
            'age_groups.*' => 'in:all,8-12,13-17,18-25,26+',
        ]);

        $s = app(\App\Services\QuestMixer::class)->generate(
            (int) $data['level_min'], (int) $data['level_max'], (int) $data['per_level'],
            $data['mix'], (int) $data['xp_easy'], (int) $data['xp_semi'],
            (int) $data['xp_complex'], (int) $data['xp_growth'],
            $data['age_groups'] ?? ['all'],
        );

        $perLevel = collect($s['per_level'])->filter()->map(fn ($n, $l) => "{$l}:{$n}")->join(' · ');

        return back()->with('success', "🎲 Mixer composed {$s['created']} draft quest" . ($s['created'] === 1 ? '' : 's') .
            ($perLevel ? " ({$perLevel})" : '') .
            ($s['skipped_full'] ? " — {$s['skipped_full']} level(s) already at target." : '') .
            ' Review them in Quests → drafts.');
    }

    public function mixLifeEvents(Request $request)
    {
        $data = $request->validate(['count' => 'required|integer|min:1|max:60']);

        $s = app(\App\Services\LifeEventMixer::class)->generate((int) $data['count']);

        return back()->with('success', "🎲 Composed {$s['created']} draft life event" . ($s['created'] === 1 ? '' : 's') .
            ($s['already'] ? " ({$s['already']} variations already exist)" : '') .
            ' — approve them in GameSet → Life Events (they arrive switched OFF).');
    }

    private function validatedBlueprint(Request $request): array
    {
        $validFieldKeys = array_column(CareerService::fields(), 'key');

        $data = $request->validate([
            'name'                    => 'required|string|max:100',
            'archetype'               => 'required|string|in:' . implode(',', array_keys(config('pesa_voice.archetypes', []))),
            'icon'                    => 'nullable|string|max:10',
            'age_group'               => 'required|in:8-12,13-17,18-25,26+,all',
            'career_fields'           => 'nullable|array',
            'career_fields.*'         => 'string|in:' . implode(',', $validFieldKeys),
            'level_min'               => 'required|integer|min:1|max:20',
            'level_max'               => 'required|integer|min:1|max:20|gte:level_min',
            'level_step'              => 'required|integer|min:1|max:10',
            'xp_base'                 => 'required|integer|min:0|max:99999',
            'xp_per_level'            => 'required|integer|min:0|max:99999',
            'kes_base'                => 'required|integer|min:0|max:9999999',
            'kes_per_level'           => 'required|integer|min:0|max:9999999',
            'steps'                   => 'required|array|min:1|max:4',
            'steps.*.type'            => 'required|string|in:' . implode(',', array_keys(QuestFactory::TRIGGER_META)),
            'steps.*.value_mode'      => 'required|in:none,any,fixed,curve',
            'steps.*.value_fixed'     => 'nullable|string|max:120',
            'steps.*.value_base'      => 'nullable|integer|min:0|max:99999999',
            'steps.*.value_per_level' => 'nullable|integer|min:0|max:99999999',
            'steps.*.label'           => 'nullable|string|max:200',
        ]);

        $data['chain']         = $request->boolean('chain');
        $data['is_active']     = $request->boolean('is_active');
        $data['icon']          = $data['icon'] ?: null;
        $data['career_fields'] = !empty($data['career_fields']) ? array_values($data['career_fields']) : null;
        $data['steps']         = array_values($data['steps']);

        return $data;
    }

    public function storeRule(Request $request)
    {
        ContractRule::create($this->validated($request));
        return back()->with('success', 'Contract rule added — it applies from the next contract each matching player generates.');
    }

    public function updateRule(Request $request, ContractRule $rule)
    {
        $rule->update($this->validated($request));
        return back()->with('success', 'Rule updated.');
    }

    public function destroyRule(ContractRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Rule deleted. Players matching no rule simply get no new contracts.');
    }

    public function toggleRule(ContractRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);
        return back()->with('success', 'Rule ' . ($rule->is_active ? 'activated' : 'paused') . '.');
    }

    /** Quest Factory switches: enabled + auto-publish + progression gate. */
    public function factorySettings(Request $request)
    {
        Setting::set(QuestFactory::ENABLED_SETTING, $request->boolean('factory_enabled') ? '1' : '0');
        Setting::set(QuestFactory::AUTOPUBLISH_SETTING, $request->boolean('factory_autopublish') ? '1' : '0');
        Setting::set(\App\Services\QuestGate::SETTING, $request->boolean('quest_gate') ? '1' : '0');

        return back()->with('success', 'Quest Factory settings saved.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'age_group'        => 'required|in:8-12,13-17,18-25,26+,all',
            'level_min'        => 'required|integer|min:1|max:99',
            'level_max'        => 'required|integer|min:1|max:99|gte:level_min',
            'objectives_min'   => 'required|integer|min:2|max:6',
            'objectives_max'   => 'required|integer|min:2|max:6|gte:objectives_min',
            'completion_mode'  => 'required|in:all,any',
            'required_count'   => 'required|integer|min:1|max:6',
            'duration_days'    => 'required|integer|min:2|max:60',
            'active_contracts' => 'required|integer|min:1|max:4',
            'reward_xp'        => 'required|integer|min:0|max:100000',
            'reward_kes'       => 'required|integer|min:0|max:1000000',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
