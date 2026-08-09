<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Arcade — Pesa Trail — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        .arc-input { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:.4rem .55rem; font-size:.76rem; color:#fff; font-family:inherit; outline:none; width:100%; }
        .arc-input:focus { border-color:rgba(245,158,11,.6); }
        select.arc-input option { background:#1a1a2e; }
        .tile-row { display:grid; grid-template-columns: 46px 56px 130px 64px 150px 64px 46px 46px 1fr; gap:6px; align-items:center; padding:.45rem .7rem; border-bottom:1px solid rgba(255,255,255,.04); }
        .tile-row:hover { background:rgba(255,255,255,.02); }
        .tile-head { font-size:9.5px; font-weight:800; letter-spacing:.07em; color:#6b7280; text-transform:uppercase; }
        .tile-num { font-weight:900; font-size:.8rem; color:#a5b4fc; text-align:center; }
        .btn-mini { font-size:11px; font-weight:800; padding:.4rem .7rem; border-radius:.55rem; cursor:pointer; white-space:nowrap; }
        .row-group { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.07); border-radius:1rem; overflow:hidden; margin-bottom:1rem; }
        .row-group-h { padding:.6rem .9rem; font-size:.75rem; font-weight:900; color:#fbbf24; background:rgba(245,158,11,.06); border-bottom:1px solid rgba(245,158,11,.15); }
        @media (max-width: 1100px) { .tile-row { grid-template-columns: repeat(2, 1fr); } .tile-head { display:none; } }

        .cal-tile {
            position:absolute; width:4.6%; aspect-ratio:1; max-width:32px; min-width:18px;
            transform:translate(-50%,-50%); background:rgba(245,158,11,.28); border:1.5px solid rgba(245,158,11,.85);
            border-radius:5px; display:flex; align-items:center; justify-content:center;
            font-size:9px; font-weight:900; color:#fff; cursor:grab; user-select:none; touch-action:none; z-index:5;
        }
        .cal-tile:hover { background:rgba(245,158,11,.5); z-index:6; }
        .cal-tile.dragging { background:rgba(139,92,246,.6); border-color:rgba(139,92,246,.9); cursor:grabbing; z-index:7; }

        .layout-tab { font-size:11.5px; font-weight:800; padding:.5rem 1rem; border-radius:.7rem 0.7rem 0 0; cursor:pointer; background:rgba(255,255,255,.03); color:#9ca3af; border:1px solid rgba(255,255,255,.08); border-bottom:none; }
        .layout-tab.active { background:rgba(245,158,11,.1); color:#fbbf24; border-color:rgba(245,158,11,.3); }
        .layout-pane { display:none; }
        .layout-pane.active { display:block; }

        /* Genuinely simulates the live forced-landscape rotation (play.blade.php's
           .panel-board: transform:rotate(90deg)) at a fixed reference phone size,
           instead of the earlier approximate "wide letterboxed frame" that didn't
           match real rotated play. Outer = the simulated phone viewport (portrait,
           minus a topbar allowance); inner rotator is pre-rotation-sized so that
           rotating it 90° exactly fills the outer frame — the same width/height
           swap the live page's transform produces. Tiles are dragged and stored in
           the SAME percentage space as the board image inside the rotator, so what
           you see here is pixel-equivalent to a real rotated phone at this size. */
        .phone-sim-outer {
            width: 380px; height: 660px; margin: 0 auto; position: relative;
            overflow: hidden; border-radius: 1.25rem; background: #0b0a16;
            border: 1px solid rgba(255,255,255,.1);
        }
        .phone-sim-rotator {
            position: absolute; top: 50%; left: 50%; width: 660px; height: 380px;
            margin-top: -190px; margin-left: -330px;
            transform: rotate(90deg);
            display: flex; align-items: center; justify-content: center;
        }
        /* Matches play.blade.php's live rotated .board-wrap exactly: no
           aspect-ratio lock, fills the rotated box completely (object-fit:fill,
           not contain) — the board image's fixed 1264:848 ratio doesn't match a
           phone's rotated proportions, and locking it here would letterbox this
           preview differently than however much any given real phone letterboxes
           (or doesn't, now that neither does), making a dragged position land in
           a different relative spot live than where it was calibrated. */
        .phone-sim-rotator .board-wrap-mobile {
            position: relative; width: 100%; height: 100%; margin: 0 auto;
        }
        .phone-sim-rotator img { width: 100%; height: 100%; display: block; object-fit: fill; }
        /* The dot's own position rotates WITH the board (correct — that's the
           whole point), but the number inside it counter-rotates back upright so
           it's actually readable while dragging. */
        .cal-tile-mobile { position:absolute; width:4.6%; aspect-ratio:1; max-width:26px; min-width:16px;
            transform:translate(-50%,-50%); background:rgba(236,72,153,.28); border:1.5px solid rgba(236,72,153,.85);
            border-radius:5px; display:flex; align-items:center; justify-content:center;
            cursor:grab; user-select:none; touch-action:none; z-index:5; }
        .cal-tile-mobile:hover { background:rgba(236,72,153,.5); z-index:6; }
        .cal-tile-mobile.dragging { background:rgba(139,92,246,.6); border-color:rgba(139,92,246,.9); cursor:grabbing; z-index:7; }
        .cal-tile-mobile span { display:block; transform:rotate(-90deg); font-size:8px; font-weight:900; color:#fff; }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'arcade'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3 text-sm">
        <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            GameSet
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold">🐍 Arcade — Pesa Trail</span>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-emerald-300" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-red-300" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    {{-- Board layout calibration --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-black text-amber-300">🎯 Board Layout — drag each square onto its real spot</h2>
        <button type="submit" form="board-calibrator" id="saveLayoutBtn" class="btn-mini text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);">💾 Save Board Layout</button>
    </div>

    <div class="flex gap-1" style="margin-bottom:-1px;">
        <div class="layout-tab active" data-tab="desktop" onclick="switchLayoutTab('desktop')">🖥️ Desktop Layout</div>
        <div class="layout-tab" data-tab="mobile" onclick="switchLayoutTab('mobile')">📱 Mobile Landscape Layout</div>
    </div>

    {{-- Both tabs post to the same endpoint — a hidden `layout` field tells the
         controller which column pair (pos_left/pos_top vs pos_left_mobile/pos_top_mobile)
         to write, so recalibrating one never touches the other. --}}
    <form id="board-calibrator" method="POST" action="{{ route('gameset.arcade.tiles.positions.save', $game) }}">
        @csrf
        <input type="hidden" name="layout" id="layoutField" value="desktop">

        <div id="desktopPane" class="layout-pane active">
            <div id="boardCalibrator" class="rounded-2xl overflow-hidden mb-2" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);position:relative;">
                <img src="{{ asset('img/game/arcade/pesatrail.webp') }}" alt="Pesa Trail board" class="w-full h-auto block" draggable="false">
                @foreach($tiles->sortBy('number') as $tile)
                <div class="cal-tile" data-number="{{ $tile->number }}" style="left:{{ $tile->pos_left ?? 50 }}%; top:{{ $tile->pos_top ?? 50 }}%;">
                    {{ $tile->number }}
                </div>
                @endforeach
            </div>
            <p class="text-[11px]" style="color:rgba(255,255,255,.35);">🖱️ Drag any numbered square onto the tile it belongs on, then click "Save Board Layout". This is what actually places the player token during a normal desktop/large-screen game.</p>
        </div>

        <div id="mobilePane" class="layout-pane">
            <div class="phone-sim-outer mb-2">
                <div class="phone-sim-rotator">
                    <div id="boardCalibratorMobile" class="board-wrap-mobile">
                        <img src="{{ asset('img/game/arcade/pesatrail.webp') }}" alt="Pesa Trail board" draggable="false">
                        @foreach($tiles->sortBy('number') as $tile)
                        <div class="cal-tile-mobile" data-number="{{ $tile->number }}" style="left:{{ $tile->pos_left_mobile ?? $tile->pos_left ?? 50 }}%; top:{{ $tile->pos_top_mobile ?? $tile->pos_top ?? 50 }}%;">
                            <span>{{ $tile->number }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <p class="text-[11px]" style="color:rgba(255,255,255,.35);">📱 This is a real, math-accurate simulation of the rotated forced-landscape play area (same 90° rotation the live game applies) at a representative phone size — not just an approximate narrow frame. Each dot's position rotates with the board exactly as it will live; its number counter-rotates so you can still read it while dragging. Calibrate here separately if tiles look off specifically on small-screen landscape play; leave uncalibrated to keep using the desktop positions as a fallback (shown pre-filled above).</p>
        </div>

        {{-- One shared set of hidden inputs, repopulated from whichever pane is
             active right before submit — see switchLayoutTab()/syncHiddenInputs(). --}}
        <div id="hiddenInputs"></div>
    </form>
    <p class="text-[11px] mb-8"></p>

    {{-- Game settings --}}
    <div class="rounded-2xl p-5 mb-8" style="background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.18);">
        <h2 class="text-sm font-black text-indigo-300 mb-3">⚙️ Game Settings</h2>
        <form method="POST" action="{{ route('gameset.arcade.settings.save', $game) }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="tile-head">Tile count<x-help-tip text="Fixed and not editable here — the board art image is built for exactly this many tiles, so the count can't change without new art." example="81" /></label>
                <div class="arc-input" style="background:rgba(255,255,255,.02);color:#9ca3af;">{{ $game->tile_count }} (fixed)</div>
            </div>
            <div>
                <label class="tile-head">Floor % (bust threshold)<x-help-tip text="If a session's pot drops to this percentage of the entry stake or below, the session busts and ends immediately. Higher = players bust out faster/more often; lower = more forgiving, longer games." example="20" /></label>
                <input name="floor_percent" type="number" min="0" max="90" value="{{ $game->floor_percent }}" class="arc-input" required>
            </div>
            <div>
                <label class="tile-head">Finish bonus %<x-help-tip text="A house-funded top-up added to the pot the instant a player reaches the final tile — the reward for actually finishing instead of cashing out early." example="15" /></label>
                <input name="finish_bonus_percent" type="number" min="0" max="100" value="{{ $game->finish_bonus_percent }}" class="arc-input" required>
            </div>
            <div>
                <label class="tile-head">XP per play<x-help-tip text="Flat XP every session earns just for playing, win or lose — stacks on top of existing position/outcome-based XP, it doesn't replace it." example="5" /></label>
                <input name="xp_per_play" type="number" min="0" max="1000" value="{{ $game->xp_per_play }}" class="arc-input" required>
            </div>
            <div>
                <label class="tile-head">XP per win<x-help-tip text="Extra flat XP awarded only on a genuine win — reaching the finish tile, or winning a Rivals Trail (head-to-head wager) round." example="20" /></label>
                <input name="xp_per_win" type="number" min="0" max="1000" value="{{ $game->xp_per_win }}" class="arc-input" required>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" id="game-active" {{ $game->is_active ? 'checked' : '' }} style="width:1.1rem;height:1.1rem;">
                <label for="game-active" class="text-xs font-bold text-gray-300">Active<x-help-tip text="Unchecking hides Pesa Trail from the arcade lobby entirely (e.g. for maintenance) without deleting any of its configuration." /></label>
            </div>
            <button type="submit" class="btn-mini text-white" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">Save Settings</button>
        </form>
        <p class="text-[11px] mt-3" style="color:rgba(255,255,255,.35);">
            <b>Floor %</b> = if the session pot drops below this % of the entry stake, the session ends (bust) — guarantees every session terminates.
            <b>Finish bonus %</b> = house-funded bonus added to the pot when a player reaches tile {{ $game->tile_count }}.
        </p>
    </div>

    {{-- Mystery outcomes --}}
    <div class="rounded-2xl p-5 mb-8" style="background:rgba(139,92,246,.05);border:1px solid rgba(139,92,246,.18);">
        <h2 class="text-sm font-black text-purple-300 mb-3">❓ Mystery Pool (gift / curse)</h2>
        <form method="POST" action="{{ route('gameset.arcade.mystery.store') }}" class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end mb-4">
            @csrf
            <input type="hidden" name="arcade_game_id" value="{{ $game->id }}">
            <div class="col-span-2"><label class="tile-head">Label<x-help-tip text="The line shown to the player when this outcome is rolled." example="Found cash on the street!" /></label><input name="label" class="arc-input" placeholder="Found cash..." required maxlength="120"></div>
            <div>
                <label class="tile-head">Effect<x-help-tip text="Gift adds this outcome's amount to the session pot; Curse removes it." example="Gift" /></label>
                <select name="effect" class="arc-input">
                    <option value="gift">🎁 Gift</option>
                    <option value="curse">💀 Curse</option>
                </select>
            </div>
            <div><label class="tile-head">Percent<x-help-tip text="How much of the CURRENT pot this outcome moves — a percentage, not a flat KES amount." example="15" /></label><input name="percent" type="number" min="1" max="100" class="arc-input" required></div>
            <div><label class="tile-head">Weight<x-help-tip text="Relative odds of this outcome being picked when a mystery tile is landed on — a weight-20 row is twice as likely to roll as a weight-10 row, regardless of how many other outcomes exist." example="10" /></label><input name="weight" type="number" min="1" max="100" value="10" class="arc-input" required></div>
            <button type="submit" class="btn-mini text-white" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">Add</button>
        </form>

        @foreach($outcomes as $o)
        <form method="POST" action="{{ route('gameset.arcade.mystery.update', $o) }}" class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-center mb-2 {{ $o->is_active ? '' : 'opacity-40' }}">
            @csrf @method('PUT')
            <input type="hidden" name="arcade_game_id" value="{{ $game->id }}">
            <div class="col-span-2"><input name="label" value="{{ $o->label }}" class="arc-input" required maxlength="120"></div>
            <select name="effect" class="arc-input">
                <option value="gift" {{ $o->effect === 'gift' ? 'selected' : '' }}>🎁 Gift</option>
                <option value="curse" {{ $o->effect === 'curse' ? 'selected' : '' }}>💀 Curse</option>
            </select>
            <input name="percent" type="number" min="1" max="100" value="{{ $o->percent }}" class="arc-input" required>
            <input name="weight" type="number" min="1" max="100" value="{{ $o->weight }}" class="arc-input" required>
            <div class="flex items-center gap-1.5">
                <label class="flex items-center gap-1 text-xs text-gray-400"><input type="checkbox" name="is_active" value="1" {{ $o->is_active ? 'checked' : '' }}> On</label>
                <button type="submit" class="btn-mini" style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;">Save</button>
                <button type="submit" form="del-myst-{{ $o->id }}" onclick="return confirm('Delete this outcome?')" class="btn-mini" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#fca5a5;">✕</button>
            </div>
        </form>
        <form id="del-myst-{{ $o->id }}" method="POST" action="{{ route('gameset.arcade.mystery.destroy', $o) }}">@csrf @method('DELETE')</form>
        @endforeach
    </div>

    {{-- Flavor text pools --}}
    <div class="rounded-2xl p-5 mb-8" style="background:rgba(245,158,11,.05);border:1px solid rgba(245,158,11,.18);">
        <h2 class="text-sm font-black text-amber-300 mb-1">💬 Flavor Text (reward / expense lessons)</h2>
        <p class="text-[11px] mb-4" style="color:rgba(255,255,255,.4);">
            One of these lines is picked at random every time a player lands on a plain reward or expense tile (unless that specific tile has its own custom Label set below, which always wins). A bigger pool means players see fresh wording more often instead of the same line every time they land on the same tile.
        </p>
        <div class="grid sm:grid-cols-2 gap-5">
            @foreach(['reward' => ['label' => '💰 Reward lines', 'texts' => $rewardTexts, 'color' => '#6ee7b7'], 'expense' => ['label' => '💸 Expense lines', 'texts' => $expenseTexts, 'color' => '#fca5a5']] as $cat => $info)
            <div>
                <p class="text-xs font-black mb-2" style="color:{{ $info['color'] }};">{{ $info['label'] }}<x-help-tip text="One line from this category's pool is picked at random whenever a player lands on a plain reward or expense tile — a bigger pool means fresher wording instead of the same line every time. A tile's own Label (set in the Tile Editor below) always overrides this pool for that specific tile." example="Consistent saving compounds — small wins add up." /></p>
                <form method="POST" action="{{ route('gameset.arcade.flavor-text.store', $game) }}" class="flex gap-2 mb-3">
                    @csrf
                    <input type="hidden" name="category" value="{{ $cat }}">
                    <input name="text" class="arc-input" placeholder="e.g. A little discipline goes a long way." required maxlength="160">
                    <button type="submit" class="btn-mini text-white flex-shrink-0" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Add</button>
                </form>
                <div class="space-y-2">
                    @forelse($info['texts'] as $t)
                    <form method="POST" action="{{ route('gameset.arcade.flavor-text.update', $t) }}" class="flex items-center gap-2 {{ $t->is_active ? '' : 'opacity-40' }}">
                        @csrf @method('PUT')
                        <input name="text" value="{{ $t->text }}" class="arc-input" required maxlength="160">
                        <label class="flex items-center gap-1 text-[10px] text-gray-400 flex-shrink-0"><input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }}> On @if($loop->first)<x-help-tip text="Uncheck to keep a line in the list without it being picked — useful for retiring a line without losing your work." />@endif</label>
                        <button type="submit" class="btn-mini flex-shrink-0" style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;">Save</button>
                        <button type="submit" form="del-flavor-{{ $t->id }}" onclick="return confirm('Delete this line?')" class="btn-mini flex-shrink-0" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#fca5a5;">✕</button>
                    </form>
                    <form id="del-flavor-{{ $t->id }}" method="POST" action="{{ route('gameset.arcade.flavor-text.destroy', $t) }}">@csrf @method('DELETE')</form>
                    @empty
                    <p class="text-[11px] text-gray-600 italic">No lines yet — falls back to a small built-in default set until you add some.</p>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Stake tiers --}}
    <div class="rounded-2xl p-5 mb-8" style="background:rgba(16,185,129,.05);border:1px solid rgba(16,185,129,.18);">
        <h2 class="text-sm font-black text-emerald-300 mb-3">💵 Stake Tiers (level → deposit amount)</h2>
        <form method="POST" action="{{ route('gameset.arcade.stake-tiers.store', $game) }}" class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-end mb-4">
            @csrf
            <div class="col-span-2"><label class="tile-head">Label<x-help-tip text="A readable name for the tier — shown only in this admin list, not to players." example="Starter (Lv 1–5)" /></label><input name="label" class="arc-input" placeholder="Starter" required maxlength="40"></div>
            <div><label class="tile-head">Level min<x-help-tip text="Lowest player level this tier applies to. A player's stake is chosen by matching their current level into one tier's min–max range." example="1" /></label><input name="level_min" type="number" min="1" max="99" class="arc-input" required></div>
            <div><label class="tile-head">Level max<x-help-tip text="Highest player level this tier applies to (must be ≥ Level min)." example="5" /></label><input name="level_max" type="number" min="1" max="99" class="arc-input" required></div>
            <div><label class="tile-head">Stake (KES)<x-help-tip text="KES deducted from the player's wallet and turned into their starting session pot when they start a game at this level range. Used for solo/normal play only — Rivals Trail wager rounds use an entry amount the round's creator sets instead." example="200" /></label><input name="stake_amount" type="number" min="1" class="arc-input" required></div>
            <button type="submit" class="btn-mini text-white" style="background:linear-gradient(135deg,#10b981,#059669);">Add</button>
        </form>

        @foreach($stakeTiers as $t)
        <form method="POST" action="{{ route('gameset.arcade.stake-tiers.update', $t) }}" class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-center mb-2 {{ $t->is_active ? '' : 'opacity-40' }}">
            @csrf @method('PUT')
            <div class="col-span-2"><input name="label" value="{{ $t->label }}" class="arc-input" required maxlength="40"></div>
            <input name="level_min" type="number" min="1" max="99" value="{{ $t->level_min }}" class="arc-input" required>
            <input name="level_max" type="number" min="1" max="99" value="{{ $t->level_max }}" class="arc-input" required>
            <input name="stake_amount" type="number" min="1" value="{{ $t->stake_amount }}" class="arc-input" required>
            <div class="flex items-center gap-1.5">
                <label class="flex items-center gap-1 text-xs text-gray-400"><input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }}> On</label>
                <button type="submit" class="btn-mini" style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;">Save</button>
                <button type="submit" form="del-tier-{{ $t->id }}" onclick="return confirm('Delete this stake tier?')" class="btn-mini" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#fca5a5;">✕</button>
            </div>
        </form>
        <form id="del-tier-{{ $t->id }}" method="POST" action="{{ route('gameset.arcade.stake-tiers.destroy', $t) }}">@csrf @method('DELETE')</form>
        @endforeach
        <p class="text-[11px] mt-2" style="color:rgba(255,255,255,.35);">A player's stake is picked by matching their current level to a tier's level range. If no tier matches, the lowest active stake is used as a fallback.</p>
    </div>

    {{-- Tile registry --}}
    <form method="POST" action="{{ route('gameset.arcade.tiles.save', $game) }}">
        @csrf
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-black text-amber-300">🎲 Tile Registry ({{ $tileCount }} tiles)</h2>
            <button type="submit" class="btn-mini text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);">💾 Save All Tiles</button>
        </div>
        <p class="text-[11px] mb-4" style="color:rgba(255,255,255,.4);">
            <b>Money</b>/<b>%</b> = what happens to the pot as a % of its CURRENT value, not a flat KES amount. <b>Movement</b>/<b>Target</b> = ladder bottom or snake head, and which tile number it sends the player to. <b>Myst.</b> routes the landing through the Mystery Pool above instead of the plain Money effect. <b>Gold</b> marks a golden tile (auto +25% of the original stake on every landing after the first). <b>Flavor text</b> here is per-tile and OPTIONAL — leave it blank to use a random pick from the Flavor Text pool above instead; only fill it in if this specific tile should always say the exact same thing every time (e.g. a sponsor-branded tile).
        </p>

        @foreach($tileGroups as $group)
        <div class="row-group">
            <div class="row-group-h">{{ $group['label'] }}</div>
            <div class="tile-row" style="background:rgba(255,255,255,.03);">
                <span class="tile-head">#</span><span class="tile-head">Icon<x-help-tip text="Optional emoji shown on this tile on the board — purely visual, has no effect on gameplay. Leave blank to use the board art's default look for that tile type." example="🎁" /></span>
                <span class="tile-head">Money<x-help-tip text="What happens to the session pot when a player lands here: None (no effect), Reward (adds to the pot), or Expense (removes from the pot) — set as a % of the pot's CURRENT value via the % field, not a flat KES amount." example="Reward" /></span><span class="tile-head">%<x-help-tip text="How much of the pot's current value the Money effect moves. Required whenever Money is set to Reward or Expense — saving with a money effect but no percent is rejected." example="10" /></span>
                <span class="tile-head">Movement<x-help-tip text="Sends the player to another tile on landing: Ladder bottom (jumps forward) or Snake head (drops back). Requires a Target tile number, and that target can't be this same tile." example="Ladder bottom" /></span><span class="tile-head">Target<x-help-tip text="The tile number this tile sends the player to. Required whenever Movement is Ladder bottom or Snake head; ignored otherwise." example="42" /></span>
                <span class="tile-head">Myst.<x-help-tip text="Routes this landing through the Mystery Pool above (a random weighted gift/curse) instead of applying the plain Money effect directly." /></span><span class="tile-head">Gold<x-help-tip text="Marks a golden tile: the first landing just reveals it, and every landing after that auto-adds +25% of the player's ORIGINAL stake to the pot." /></span>
                <span class="tile-head">Flavor text<x-help-tip text="Optional per-tile override line. Leave blank to use a random pick from the Flavor Text pool above instead; only fill this in if this specific tile should always say the exact same thing every time, e.g. a sponsor-branded tile." example="Sponsored by SafeSave Bank!" /></span>
            </div>
            @foreach($group['tiles'] as $tile)
            <div class="tile-row">
                <input type="hidden" name="tiles[{{ $tile->number }}][number]" value="{{ $tile->number }}">
                <span class="tile-num">{{ $tile->number }}</span>
                <input name="tiles[{{ $tile->number }}][icon]" value="{{ $tile->icon }}" class="arc-input" maxlength="10" style="text-align:center;">
                <select name="tiles[{{ $tile->number }}][money_effect]" class="arc-input">
                    @foreach(\App\Models\ArcadeTile::MONEY_EFFECTS as $k => $lbl)
                    <option value="{{ $k }}" {{ $tile->money_effect === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <input type="number" name="tiles[{{ $tile->number }}][money_percent]" value="{{ $tile->money_percent }}" min="0" max="100" class="arc-input">
                <select name="tiles[{{ $tile->number }}][movement_role]" class="arc-input">
                    @foreach(\App\Models\ArcadeTile::MOVEMENT_ROLES as $k => $lbl)
                    <option value="{{ $k }}" {{ $tile->movement_role === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <input type="number" name="tiles[{{ $tile->number }}][target_number]" value="{{ $tile->target_number }}" min="1" max="{{ $game->tile_count }}" class="arc-input">
                <input type="checkbox" name="tiles[{{ $tile->number }}][is_mystery]" value="1" {{ $tile->is_mystery ? 'checked' : '' }} style="width:1rem;height:1rem;justify-self:center;">
                <input type="checkbox" name="tiles[{{ $tile->number }}][is_golden]" value="1" {{ $tile->is_golden ? 'checked' : '' }} style="width:1rem;height:1rem;justify-self:center;">
                <input name="tiles[{{ $tile->number }}][label]" value="{{ $tile->label }}" class="arc-input" maxlength="120" placeholder="flavor text">
            </div>
            @endforeach
        </div>
        @endforeach

        <button type="submit" class="btn-mini text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);">💾 Save All Tiles</button>
    </form>

</div>

<script>
    // Wires drag-to-place on one calibrator board — called once per pane
    // (desktop, mobile) so both share identical behavior against their own container.
    function wireCalibrator(boardId) {
        const board = document.getElementById(boardId);
        if (!board) return;

        function toPercent(clientX, clientY) {
            const rect = board.getBoundingClientRect();
            const left = Math.max(0, Math.min(100, (clientX - rect.left) / rect.width * 100));
            const top  = Math.max(0, Math.min(100, (clientY - rect.top) / rect.height * 100));
            return { left, top };
        }

        board.querySelectorAll('.cal-tile').forEach(tile => {
            tile.addEventListener('pointerdown', e => {
                tile.classList.add('dragging');
                try { tile.setPointerCapture(e.pointerId); } catch (_) {}
                e.preventDefault();
            });
            tile.addEventListener('pointermove', e => {
                if (!tile.classList.contains('dragging')) return;
                const { left, top } = toPercent(e.clientX, e.clientY);
                tile.style.left = left.toFixed(2) + '%';
                tile.style.top = top.toFixed(2) + '%';
            });
            tile.addEventListener('pointerup', () => tile.classList.remove('dragging'));
            tile.addEventListener('pointercancel', () => tile.classList.remove('dragging'));
        });
    }
    wireCalibrator('boardCalibrator');

    // The mobile pane's board is CSS-rotated 90° (see .phone-sim-rotator) to
    // accurately simulate live rotated play — so converting a pointer's
    // screen position into the tile's percentage position needs the rotated
    // equivalent of the plain formula above, not the plain formula itself.
    // Derived by hand-tracing a 90°-clockwise rotation: local "left%" runs
    // along the screen's Y axis and local "top%" runs BACKWARDS along the
    // screen's X axis. (Verified: this depends only on the rotation angle,
    // not on where the transform-origin is, so it holds regardless of the
    // exact recipe play.blade.php's real board uses.)
    function wireCalibratorRotated(boardId) {
        const board = document.getElementById(boardId);
        if (!board) return;

        function toPercent(clientX, clientY) {
            const rect = board.getBoundingClientRect();
            const left = Math.max(0, Math.min(100, (clientY - rect.top) / rect.height * 100));
            const top  = Math.max(0, Math.min(100, 100 - (clientX - rect.left) / rect.width * 100));
            return { left, top };
        }

        board.querySelectorAll('.cal-tile-mobile').forEach(tile => {
            tile.addEventListener('pointerdown', e => {
                tile.classList.add('dragging');
                try { tile.setPointerCapture(e.pointerId); } catch (_) {}
                e.preventDefault();
            });
            tile.addEventListener('pointermove', e => {
                if (!tile.classList.contains('dragging')) return;
                const { left, top } = toPercent(e.clientX, e.clientY);
                tile.style.left = left.toFixed(2) + '%';
                tile.style.top = top.toFixed(2) + '%';
            });
            tile.addEventListener('pointerup', () => tile.classList.remove('dragging'));
            tile.addEventListener('pointercancel', () => tile.classList.remove('dragging'));
        });
    }
    wireCalibratorRotated('boardCalibratorMobile');

    function switchLayoutTab(name) {
        document.querySelectorAll('.layout-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
        document.getElementById('desktopPane').classList.toggle('active', name === 'desktop');
        document.getElementById('mobilePane').classList.toggle('active', name === 'mobile');
        document.getElementById('layoutField').value = name;
        document.getElementById('saveLayoutBtn').textContent = name === 'mobile' ? '💾 Save Mobile Landscape Layout' : '💾 Save Board Layout';
    }

    // Serializes whichever pane is currently active into hidden inputs right
    // before submit — the two panes never submit at the same time, so only
    // one set of positions[...] fields ever needs to exist at once.
    document.getElementById('board-calibrator').addEventListener('submit', () => {
        const activeLayout = document.getElementById('layoutField').value;
        const paneId = activeLayout === 'mobile' ? 'boardCalibratorMobile' : 'boardCalibrator';
        const tileClass = activeLayout === 'mobile' ? '.cal-tile-mobile' : '.cal-tile';
        const pane = document.getElementById(paneId);
        const container = document.getElementById('hiddenInputs');
        container.innerHTML = '';
        pane.querySelectorAll(tileClass).forEach(tile => {
            const n = tile.dataset.number;
            const left = parseFloat(tile.style.left) || 50;
            const top = parseFloat(tile.style.top) || 50;
            ['number', 'pos_left', 'pos_top'].forEach(field => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `positions[${n}][${field}]`;
                input.value = field === 'number' ? n : (field === 'pos_left' ? left.toFixed(2) : top.toFixed(2));
                container.appendChild(input);
            });
        });
    });
</script>
</body>
</html>
