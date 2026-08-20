<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeTemplate;
use App\Services\ChallengeService;
use Illuminate\Http\Request;

class GamesetChallengeController extends Controller
{
    public function index()
    {
        $templates = ChallengeTemplate::orderBy('name')->get();

        // ALL challenges (official broadcasts + player/teacher/chairman-created
        // ones), not just official — admin oversight needs to be able to
        // deactivate any of them, not only the ones GameSet itself launched.
        $challenges = Challenge::whereIn('status', ['pending', 'active'])
            ->orderByDesc('created_at')
            ->withCount(['participants'])
            ->limit(30)
            ->get();

        $stats = [
            'templates' => ChallengeTemplate::count(),
            'active'    => Challenge::where('status', 'active')->count(),
            'official'  => Challenge::where('is_official', true)->count(),
            'completed' => Challenge::where('status', 'completed')->count(),
        ];

        return view('gameset.challenges.index', compact('templates', 'challenges', 'stats'));
    }

    public function create()
    {
        return view('gameset.challenges.form', ['mode' => 'create', 'template' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);
        ChallengeTemplate::create($data);

        return redirect()->route('gameset.challenges.index')->with('success', "Template \"{$data['name']}\" created.");
    }

    public function edit(ChallengeTemplate $template)
    {
        return view('gameset.challenges.form', ['mode' => 'edit', 'template' => $template]);
    }

    public function update(Request $request, ChallengeTemplate $template)
    {
        $data = $this->validateTemplate($request);
        $template->update($data);

        return redirect()->route('gameset.challenges.index')->with('success', "Template \"{$template->name}\" updated.");
    }

    public function destroy(ChallengeTemplate $template)
    {
        $name = $template->name;
        $template->delete();

        return redirect()->route('gameset.challenges.index')->with('success', "Template \"{$name}\" deleted.");
    }

    public function toggleActive(ChallengeTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return response()->json(['is_active' => $template->is_active]);
    }

    /** Launch a "PesaCity Challenge" — official, open-to-all broadcast. */
    public function launchOfficial(Request $request, ChallengeService $service)
    {
        $data = $request->validate([
            'template_id'      => 'required|exists:challenge_templates,id',
            'title'            => 'nullable|string|max:150',
            'duration_days'    => 'nullable|integer|min:1|max:60',
            'goal'             => 'nullable|numeric|min:0',
            'stake_amount'     => 'nullable|integer|min:0',
            'level_min'        => 'nullable|integer|min:1|max:99',
            'level_max'        => 'nullable|integer|min:1|max:99',
            'is_chama_battle'  => 'boolean',
        ]);

        $isChamaBattle = $request->boolean('is_chama_battle');
        $template = ChallengeTemplate::findOrFail($data['template_id']);

        $service->createBroadcast($template, [
            'title'           => ($data['title'] ?? null) ?: ($isChamaBattle
                ? "⚔️ Chama Battle — {$template->name}"
                : "🏆 PesaCity Challenge — {$template->name}"),
            'is_official'     => true,
            'is_chama_battle' => $isChamaBattle,
            'scope'           => 'open',
            'duration_days'   => $data['duration_days'] ?? null,
            'goal'            => $data['goal'] ?? null,
            'stake_amount'    => $data['stake_amount'] ?? null,
            'level_min'       => $data['level_min'] ?? null,
            'level_max'       => $data['level_max'] ?? null,
        ]);

        return redirect()->route('gameset.challenges.index')->with('success', $isChamaBattle
            ? 'Chama Battle launched — chairmen can now enter their chamas from Champions\' Court.'
            : 'PesaCity Challenge launched — players can join it now from Champions\' Court.');
    }

    /** Admin oversight: deactivate ANY in-progress challenge (official or player-created) — refunds any paid stakes. */
    public function cancel(Challenge $challenge, ChallengeService $service)
    {
        if (in_array($challenge->status, ['pending', 'active'], true)) {
            $service->cancelChallenge($challenge, 'An admin deactivated this challenge from GameSet Hub. Any entry fee has been refunded.');
        }

        return redirect()->route('gameset.challenges.index')->with('success', 'Challenge cancelled.');
    }

    private function validateTemplate(Request $request): array
    {
        $data = $request->validate([
            'key'         => 'required|string|max:60|alpha_dash',
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:300',
            'metric'      => 'required|in:net_worth,savings_balance,wallet_balance,xp_points,courses_completed,assets_owned,jobs_started,gigs_completed,chama_contributions,friends_count,forum_posts,bills_paid,arcade_wins,arcade_winnings,shares_bought,shares_profit',
            'style'       => 'required|in:percent,amount,count',
            'icon'        => 'nullable|string|max:10',
            'image_url'   => 'nullable|string|max:500',
            'default_duration_days' => 'required|integer|min:1|max:60',
            'level_min'   => 'required|integer|min:1|max:99',
            'level_max'   => 'required|integer|min:1|max:99',
            'allow_player_created' => 'boolean',
            'allow_broadcast'      => 'boolean',
            'is_active'            => 'boolean',
        ]);

        $data['icon']                  = $data['icon'] ?: '🏆';
        $data['allow_player_created']  = $request->boolean('allow_player_created');
        $data['allow_broadcast']       = $request->boolean('allow_broadcast');
        $data['is_active']             = $request->boolean('is_active');

        return $data;
    }
}
