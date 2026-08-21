<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'edit' ? 'Edit' : 'New' }} Challenge Template — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        label { display:block; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:.35rem; }
        input, select, textarea {
            width:100%; padding:.6rem .8rem; border-radius:.7rem; background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.12); color:#fff; font-size:.9rem;
        }
        input:focus, select:focus, textarea:focus { outline:none; border-color:rgba(99,102,241,.5); }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'challenges'])

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
    <a href="{{ route('gameset.challenges.index') }}" class="text-gray-400 hover:text-white text-sm mb-4 inline-flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Challenges
    </a>
    <h1 class="text-2xl font-black mb-6">{{ $mode === 'edit' ? '✏️ Edit Template' : '⚔️ New Challenge Template' }}</h1>

    <form method="POST" action="{{ $mode === 'edit' ? route('gameset.challenges.update', $template) : route('gameset.challenges.store') }}" class="space-y-5">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Key (unique, no spaces)
                    <x-help-tip text="A stable machine identifier used internally to reference this template — set once at creation and can never be changed afterward." example="savings-sprint" />
                </label>
                <input type="text" name="key" value="{{ old('key', $template->key ?? '') }}" required maxlength="60" {{ $mode === 'edit' ? 'readonly' : '' }}>
            </div>
            <div>
                <label>Name
                    <x-help-tip text="The challenge's title, shown to players when they start or join a challenge based on this template." example="Savings Sprint" />
                </label>
                <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" required maxlength="120">
            </div>
        </div>
        <div>
            <label>Description
                <x-help-tip text="Explains the challenge's goal to players in plain language." example="Whoever grows their savings balance the most (by %) in a week wins." />
            </label>
            <textarea name="description" rows="2" maxlength="300">{{ old('description', $template->description ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Metric
                    <x-help-tip text="What progress is scored during the challenge — pick one every eligible player can still move, since a maxed-out metric (e.g. Courses Completed for a player who's finished them all) makes the contest unfair. Less obvious options: XP Points = total experience earned, Gigs Completed = one-off freelance tasks (not ongoing jobs), Chama Contributions = total paid into chama savings groups, Arcade Wins/Winnings = Pesa Trail board-game wins and net Ksh won." example="savings_balance" />
                </label>
                <select name="metric" required>
                    @foreach(['net_worth'=>'Net Worth','savings_balance'=>'Savings Balance','wallet_balance'=>'Wallet Balance','xp_points'=>'XP Points','courses_completed'=>'Courses Completed','assets_owned'=>'Assets Owned','jobs_started'=>'Jobs Started','gigs_completed'=>'Gigs Completed','chama_contributions'=>'Chama Contributions','friends_count'=>'Friends Count','forum_posts'=>'Forum Posts','bills_paid'=>'Bills Paid','arcade_wins'=>'Arcade Wins (Pesa Trail)','arcade_winnings'=>'Arcade Winnings (Pesa Trail)','shares_bought'=>'Shares Bought','shares_profit'=>'Shares Profit'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('metric', $template->metric ?? 'net_worth') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Style
                    <x-help-tip text="The fairness mechanism: Percent growth scores your % improvement over your own starting value — fairest, since it lets a rich and poor player compete on equal footing. Absolute amount scores raw KES/points gained (favors players who started with more); Count scores raw event occurrences and suits activity metrics like bills paid." example="percent" />
                </label>
                <select name="style" required>
                    <option value="percent" @selected(old('style', $template->style ?? 'percent') === 'percent')>Percent growth (fairest)</option>
                    <option value="amount" @selected(old('style', $template->style ?? '') === 'amount')>Absolute amount</option>
                    <option value="count" @selected(old('style', $template->style ?? '') === 'count')>Count</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Emoji Icon
                    <x-help-tip text="Used as the challenge's visual when no Image URL is set below." example="🏆" />
                </label>
                <input type="text" name="icon" value="{{ old('icon', $template->icon ?? '🏆') }}" maxlength="10">
            </div>
            <div>
                <label>Image URL (overrides emoji)
                    <x-help-tip text="Overrides the emoji with a custom image — use the in-house SVG trophy set for a consistent look." example="/img/trophies/duel.svg" />
                </label>
                <input type="text" name="image_url" value="{{ old('image_url', $template->image_url ?? '') }}" placeholder="/img/trophies/...svg">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label>Default Duration (days)
                    <x-help-tip text="How many days a challenge started from this template runs by default — long enough to matter, short enough to stay urgent. Set to 0 for All-Time: the challenge never hits a deadline and just runs as a permanent leaderboard." example="7" />
                </label>
                <input type="number" name="default_duration_days" value="{{ old('default_duration_days', $template->default_duration_days ?? 7) }}" min="0" max="60" required>
                <p class="text-[.6rem] text-gray-500 mt-1">0 = All-Time (never ends)</p>
            </div>
            <div>
                <label>Min Level
                    <x-help-tip text="The lowest player level allowed to join or be challenged with this template." example="3" />
                </label>
                <input type="number" name="level_min" value="{{ old('level_min', $template->level_min ?? 1) }}" min="1" max="99" required>
            </div>
            <div>
                <label>Max Level
                    <x-help-tip text="The highest player level allowed to join or be challenged with this template — keep the band narrow enough that the metric stays a fair contest." example="99" />
                </label>
                <input type="number" name="level_max" value="{{ old('level_max', $template->level_max ?? 99) }}" min="1" max="99" required>
            </div>
        </div>
        <div class="flex flex-wrap gap-6">
            <label class="flex items-center gap-2 !mb-0">
                <input type="checkbox" name="allow_player_created" value="1" class="!w-auto" @checked(old('allow_player_created', $template->allow_player_created ?? true))>
                <span class="text-white text-sm font-semibold normal-case">Players can create duels from this<x-help-tip text="When on, players can invite a friend to a 1-on-1 or team duel using this template; turn off for templates meant only for official/teacher use." example="On" /></span>
            </label>
            <label class="flex items-center gap-2 !mb-0">
                <input type="checkbox" name="allow_broadcast" value="1" class="!w-auto" @checked(old('allow_broadcast', $template->allow_broadcast ?? true))>
                <span class="text-white text-sm font-semibold normal-case">Usable for official/class broadcasts<x-help-tip text="When on, this template is available to the admin/teacher 'start an official challenge' tool for open, join-anytime broadcast challenges." example="On" /></span>
            </label>
            <label class="flex items-center gap-2 !mb-0">
                <input type="checkbox" name="is_active" value="1" class="!w-auto" @checked(old('is_active', $template->is_active ?? true))>
                <span class="text-white text-sm font-semibold normal-case">Active<x-help-tip text="Controls whether this template can be used to start any new challenge; existing challenges already running are unaffected." example="On" /></span>
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 rounded-xl font-black text-white" style="background:linear-gradient(135deg,#6366f1,#4338ca);">
                {{ $mode === 'edit' ? 'Save Changes' : 'Create Template' }}
            </button>
            <a href="{{ route('gameset.challenges.index') }}" class="px-5 py-2.5 rounded-xl font-bold text-gray-300" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
