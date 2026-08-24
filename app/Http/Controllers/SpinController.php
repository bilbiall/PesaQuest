<?php

namespace App\Http\Controllers;

use App\Models\SpinResult;
use App\Services\PlanGate;
use App\Services\QuestTriggerService;
use Illuminate\Http\Request;

class SpinController extends Controller
{
    // Segments are editable in GameSet Hub → Spin Wheel (spin_segments table).
    // SpinSegment::wheelSegments() returns them in render order, falling back
    // to the original hardcoded set until the table is migrated/seeded.

    public function index()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        try {
            $canSpin  = SpinResult::canSpinToday($user->id);
            $lastSpin = SpinResult::where('user_id', $user->id)->latest()->first();
            $history  = SpinResult::where('user_id', $user->id)->latest()->limit(5)->get();

            // Plan gate: admin-configurable cooldown (Free Plan Gates), default
            // free = every 7 days, premium = every real day (0 = no extra cooldown).
            $spinCooldown = app(PlanGate::class)->limit($user, 'spin_cooldown_days');
            if ($canSpin && $lastSpin && $spinCooldown > 0) {
                $canSpin = $lastSpin->created_at->lte(now()->subDays($spinCooldown));
            }
        } catch (\Exception $e) {
            // spin_results table not yet migrated — degrade gracefully
            $canSpin      = false;
            $lastSpin     = null;
            $history      = collect();
            $spinCooldown = 0;
        }

        // When the wheel's locked, the exact moment it unlocks again — the
        // later of "midnight after the last spin" and "the plan's cooldown
        // has elapsed" — so the countdown chip is never optimistic for a
        // free account still under a multi-day cooldown.
        $nextSpinAt = null;
        if (!$canSpin && $lastSpin) {
            $nextSpinAt = $lastSpin->created_at->copy()->startOfDay()->addDay();
            if ($spinCooldown > 0) {
                $cooldownEnd = $lastSpin->created_at->copy()->addDays($spinCooldown);
                if ($cooldownEnd->gt($nextSpinAt)) {
                    $nextSpinAt = $cooldownEnd;
                }
            }
        }

        return view('spin.wheel', [
            'progress'   => $progress,
            'canSpin'    => $canSpin,
            'lastSpin'   => $lastSpin,
            'history'    => $history,
            'nextSpinAt' => $nextSpinAt,
            'segments'   => \App\Models\SpinSegment::wheelSegments($progress->level ?? 1),
        ]);
    }

    public function spin(Request $request)
    {
        $user = auth()->user();

        try {
            $alreadySpun = !SpinResult::canSpinToday($user->id);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Migrations pending — run php artisan migrate first.'], 503);
        }

        if ($alreadySpun) {
            return response()->json(['success' => false, 'message' => 'You have already spun today. Come back tomorrow!'], 429);
        }

        // Plan gate: admin-configurable cooldown (Free Plan Gates), 0 = no extra cooldown
        $gate         = app(PlanGate::class);
        $spinCooldown = $gate->limit($user, 'spin_cooldown_days');
        if ($spinCooldown > 0) {
            $lastSpin = SpinResult::where('user_id', $user->id)->latest()->first();
            if ($lastSpin && $lastSpin->created_at->gt(now()->subDays($spinCooldown))) {
                return response()->json($gate->deny('spin_cooldown_days', $spinCooldown) + ['success' => false, 'message' => "Free accounts spin every {$spinCooldown} days — subscribe to spin every day!"], 429);
            }
        }

        $progress = $user->getOrCreateProgress();

        // Weighted random selection (same ordered list the wheel view rendered,
        // so segment_index animates to the right wedge)
        $segments = \App\Models\SpinSegment::wheelSegments($progress->level ?? 1);
        $index    = $this->weightedPick($segments);
        $prize    = $segments[$index];
        $balanceBefore = $progress->balance ?? 0;
        $balanceAfter  = $balanceBefore;

        // Apply prize
        switch ($prize['type']) {
            case 'balance':
                // Never clamp to 0 — a fine landing on an empty balance must still
                // go negative, so it's a real debt that future income pays down,
                // not a bad outcome that's silently discarded.
                $balanceAfter = $balanceBefore + $prize['value'];
                $progress->balance = $balanceAfter;
                break;
            case 'credit':
                $progress->adjustCreditScore($prize['value']);
                break;
            case 'xp':
                $progress->points_total = ($progress->points_total ?? 0) + $prize['value'];
                $progress->level = $progress->calculateLevel();
                break;
            case 'salary_2x':
                // Flag for next salary to be doubled — store in active_loans JSON as a flag
                $flags = $progress->active_loans ?? [];
                if (!is_array($flags)) $flags = json_decode($flags, true) ?? [];
                $flags['salary_2x'] = true;
                $progress->active_loans = $flags;
                break;
            case 'badge':
                // TODO: award badge if badge system is integrated
                break;
        }

        $progress->save();

        try {
            SpinResult::create([
                'user_id'       => $user->id,
                'prize_label'   => $prize['label'],
                'prize_type'    => $prize['type'],
                'prize_value'   => $prize['value'],
                'prize_emoji'   => $prize['emoji'],
                'prize_tier'    => $prize['tier'],
                'balance_before'=> $balanceBefore,
                'balance_after' => $balanceAfter,
                'segment_index' => $index,
            ]);
        } catch (\Exception $e) {
            // Table missing — prize was still applied to balance, just not logged
        }

        app(QuestTriggerService::class)->fire($user, 'spin_wheel');

        return response()->json([
            'success'       => true,
            'segment_index' => $index,
            'prize'         => $prize,
            'new_balance'   => $progress->fresh()->balance,
            'balance_delta' => $balanceAfter - $balanceBefore,
        ]);
    }

    private function weightedPick(array $items): int
    {
        $pool = [];
        foreach ($items as $i => $item) {
            for ($w = 0; $w < $item['weight']; $w++) {
                $pool[] = $i;
            }
        }
        return $pool[array_rand($pool)];
    }
}
