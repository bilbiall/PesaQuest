<?php

namespace App\Http\Controllers;

use App\Models\ArcadeFlavorText;
use App\Models\ArcadeGame;
use App\Models\ArcadeMysteryOutcome;
use App\Models\ArcadeStakeTier;
use App\Models\ArcadeTile;
use Illuminate\Http\Request;

/**
 * GameSet → Arcade: quick filler mini-games that spend/pay out of a session
 * pot rather than the wallet directly. Snakes & Cash is the first game — an
 * admin-editable tile registry (money effect + movement role + mystery/golden
 * flags, independently stackable per tile) plus a gift/curse mystery pool.
 */
class GamesetArcadeController extends Controller
{
    /** Physical board rows (bottom to top), used only to group the editor for readability. */
    private const ROW_BOUNDS = [[1, 13], [14, 27], [28, 40], [41, 54], [55, 68], [69, 81]];

    public function index()
    {
        $game  = ArcadeGame::with(['tiles', 'mysteryOutcomes'])->where('slug', 'snakes-and-cash')->firstOrFail();
        $tiles = $game->tiles;

        $tileGroups = [];
        foreach (self::ROW_BOUNDS as $i => [$from, $to]) {
            $tileGroups[] = [
                'label' => "Row " . ($i + 1) . " (tiles {$from}–{$to})",
                'tiles' => $tiles->whereBetween('number', [$from, $to])->values(),
            ];
        }

        return view('gameset.arcade.index', [
            'game'       => $game,
            'tiles'      => $tiles,
            'tileGroups' => $tileGroups,
            'tileCount'  => $tiles->count(),
            'outcomes'   => $game->mysteryOutcomes,
            'stakeTiers' => ArcadeStakeTier::where('arcade_game_id', $game->id)->orderBy('level_min')->get(),
            'rewardTexts' => ArcadeFlavorText::where('arcade_game_id', $game->id)->where('category', 'reward')->latest()->get(),
            'expenseTexts' => ArcadeFlavorText::where('arcade_game_id', $game->id)->where('category', 'expense')->latest()->get(),
        ]);
    }

    public function saveSettings(Request $request, ArcadeGame $game)
    {
        $data = $request->validate([
            'floor_percent'        => 'required|integer|min:0|max:90',
            'finish_bonus_percent' => 'required|integer|min:0|max:100',
            'xp_per_play'          => 'required|integer|min:0|max:1000',
            'xp_per_win'           => 'required|integer|min:0|max:1000',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $game->update($data);

        return back()->with('success', 'Game settings saved.');
    }

    /** Drag-to-place calibration for the board art overlay — independent of the tile
     *  effects form below so nudging a misaligned square never risks the money/movement
     *  config also on the page. Each tile must already exist (seeded at 1..tile_count). */
    /** `layout` picks which column pair this save writes: the normal desktop
     *  pos_left/pos_top, or the separate pos_left_mobile/pos_top_mobile used
     *  only by the forced-landscape mobile board — recalibrating one never
     *  touches the other, and mobile falls back to the desktop values until
     *  an admin explicitly calibrates it. */
    public function saveTilePositions(Request $request, ArcadeGame $game)
    {
        $data = $request->validate([
            'layout'                => 'nullable|in:desktop,mobile',
            'positions'             => 'required|array|size:' . $game->tile_count,
            'positions.*.number'    => 'required|integer|min:1|max:' . $game->tile_count,
            'positions.*.pos_left'  => 'required|numeric|min:0|max:100',
            'positions.*.pos_top'   => 'required|numeric|min:0|max:100',
        ]);

        $isMobile = ($data['layout'] ?? 'desktop') === 'mobile';
        [$leftCol, $topCol] = $isMobile ? ['pos_left_mobile', 'pos_top_mobile'] : ['pos_left', 'pos_top'];

        $byNumber = $game->tiles->keyBy('number');
        foreach ($data['positions'] as $row) {
            $tile = $byNumber->get((int) $row['number']);
            $tile?->update([$leftCol => $row['pos_left'], $topCol => $row['pos_top']]);
        }

        $label = $isMobile ? 'Mobile landscape layout' : 'Board layout';
        return back()->with('success', "{$label} saved — " . count($data['positions']) . ' tile positions updated.');
    }

    public function saveTiles(Request $request, ArcadeGame $game)
    {
        $data = $request->validate([
            'tiles'                    => 'required|array|size:' . $game->tile_count,
            'tiles.*.number'           => 'required|integer|min:1|max:' . $game->tile_count,
            'tiles.*.money_effect'     => 'required|in:' . implode(',', array_keys(ArcadeTile::MONEY_EFFECTS)),
            'tiles.*.money_percent'    => 'nullable|integer|min:0|max:100',
            'tiles.*.movement_role'    => 'required|in:' . implode(',', array_keys(ArcadeTile::MOVEMENT_ROLES)),
            'tiles.*.target_number'    => 'nullable|integer|min:1|max:' . $game->tile_count,
            'tiles.*.is_mystery'       => 'nullable|boolean',
            'tiles.*.is_golden'        => 'nullable|boolean',
            'tiles.*.icon'             => 'nullable|string|max:10',
            'tiles.*.label'            => 'nullable|string|max:120',
        ]);

        $byNumber = $game->tiles->keyBy('number');
        $errors   = [];

        foreach ($data['tiles'] as $row) {
            $number = (int) $row['number'];
            $tile   = $byNumber->get($number);
            if (!$tile) continue;

            $hasMovement = $row['movement_role'] !== 'none';
            if ($hasMovement && empty($row['target_number'])) {
                $errors[] = "Tile {$number} has a movement role but no target tile.";
                continue;
            }
            if ($hasMovement && (int) $row['target_number'] === $number) {
                $errors[] = "Tile {$number} can't target itself.";
                continue;
            }
            if ($row['money_effect'] !== 'none' && empty($row['money_percent'])) {
                $errors[] = "Tile {$number} has a money effect but no percent.";
                continue;
            }

            $tile->update([
                'money_effect'  => $row['money_effect'],
                'money_percent' => $row['money_effect'] === 'none' ? null : (int) $row['money_percent'],
                'movement_role' => $row['movement_role'],
                'target_number' => $hasMovement ? (int) $row['target_number'] : null,
                'is_mystery'    => (bool) ($row['is_mystery'] ?? false),
                'is_golden'     => (bool) ($row['is_golden'] ?? false),
                'icon'          => $row['icon'] ?? null,
                'label'         => $row['label'] ?? null,
            ]);
        }

        if ($errors) {
            return back()->withErrors($errors);
        }

        return back()->with('success', 'Board saved — ' . count($data['tiles']) . ' tiles updated.');
    }

    public function storeMystery(Request $request, ArcadeGame $game)
    {
        $game->mysteryOutcomes()->create($this->validateOutcome($request));
        return back()->with('success', 'Mystery outcome added.');
    }

    public function updateMystery(Request $request, ArcadeMysteryOutcome $outcome)
    {
        $outcome->update($this->validateOutcome($request));
        return back()->with('success', 'Mystery outcome updated.');
    }

    public function destroyMystery(ArcadeMysteryOutcome $outcome)
    {
        $outcome->delete();
        return back()->with('success', 'Mystery outcome deleted.');
    }

    // ── Flavor text: the randomly-picked reward/expense lesson pool ──
    public function storeFlavorText(Request $request, ArcadeGame $game)
    {
        $data = $request->validate([
            'category' => 'required|in:reward,expense',
            'text'     => 'required|string|max:160',
        ]);
        $game->flavorTexts()->create($data + ['is_active' => true]);

        return back()->with('success', 'Flavor text added.');
    }

    public function updateFlavorText(Request $request, ArcadeFlavorText $flavorText)
    {
        $data = $request->validate(['text' => 'required|string|max:160']);
        $data['is_active'] = $request->boolean('is_active', true);
        $flavorText->update($data);

        return back()->with('success', 'Flavor text updated.');
    }

    public function destroyFlavorText(ArcadeFlavorText $flavorText)
    {
        $flavorText->delete();
        return back()->with('success', 'Flavor text deleted.');
    }

    private function validateOutcome(Request $request): array
    {
        $data = $request->validate([
            'label'   => 'required|string|max:120',
            'effect'  => 'required|in:gift,curse',
            'percent' => 'required|integer|min:1|max:100',
            'weight'  => 'required|integer|min:1|max:100',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    // ── Stake tiers: how much a player's level deposits into the session pot ──

    public function storeStakeTier(Request $request, ArcadeGame $game)
    {
        $game->stakeTiers()->create($this->validateStakeTier($request));
        return back()->with('success', 'Stake tier added.');
    }

    public function updateStakeTier(Request $request, ArcadeStakeTier $stakeTier)
    {
        $stakeTier->update($this->validateStakeTier($request));
        return back()->with('success', 'Stake tier updated.');
    }

    public function destroyStakeTier(ArcadeStakeTier $stakeTier)
    {
        $stakeTier->delete();
        return back()->with('success', 'Stake tier deleted.');
    }

    private function validateStakeTier(Request $request): array
    {
        $data = $request->validate([
            'label'         => 'required|string|max:40',
            'level_min'     => 'required|integer|min:1|max:99',
            'level_max'     => 'required|integer|min:1|max:99|gte:level_min',
            'stake_amount'  => 'required|integer|min:1|max:1000000',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
