<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Friends — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
        .fr-card { background:#110f28; border:1px solid rgba(255,255,255,0.08); }
        .fr-pill { font-size:11px; font-weight:900; padding:6px 14px; border-radius:10px; cursor:pointer;
                   background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); color:#d1d5db; transition:all .15s; }
        .fr-pill:hover, .fr-pill.on { background:rgba(99,102,241,0.2); border-color:rgba(99,102,241,0.55); color:#fff; }
    </style>
</head>
<body class="text-white min-h-screen"
      x-data="{ loanOpen: false, loanFriendId: null, loanFriendName: '', loanAmount: null, loanTerm: null,
                openLoanModal(id, name) { this.loanFriendId = id; this.loanFriendName = name; this.loanAmount = null; this.loanTerm = null; this.loanOpen = true; },
                giftOpen: false, giftFriendId: null, giftFriendName: '', giftAmount: null, giftMessage: '',
                openGiftModal(id, name) { this.giftFriendId = id; this.giftFriendName = name; this.giftAmount = null; this.giftMessage = ''; this.giftOpen = true; } }">

{{-- ── Nav ── --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Dashboard
        </a>
        <div class="flex items-center gap-4">
            <a href="{{ route('forums.index') }}" class="text-xs font-bold text-gray-400 hover:text-white transition-colors inline-flex items-center gap-1"><x-icon name="speech" class="w-3.5 h-3.5" /> Forums</a>
            <a href="{{ route('chama.index') }}" class="text-xs font-bold text-gray-400 hover:text-white transition-colors inline-flex items-center gap-1"><x-icon name="group" class="w-3.5 h-3.5" /> Chamas</a>
        </div>
    </div>
</nav>

{{-- ── Hero ── --}}
<div class="border-b border-white/5 py-10"
     style="background: linear-gradient(135deg, rgba(99,102,241,0.10) 0%, rgba(16,185,129,0.05) 100%);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-black mb-2 inline-flex items-center gap-2"><x-icon name="people" class="w-8 h-8" /> Friends</h1>
            <p class="text-gray-400">Team up: lend and borrow with agreed rates, and build chamas together.</p>
        </div>
        @if($code)
        <div class="rounded-2xl px-4 py-3 text-center" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.35);">
            <div class="text-[10px] font-black uppercase tracking-wider text-indigo-300">My friend code</div>
            <div class="flex items-center gap-2 mt-1">
                <span id="fr-code" class="text-lg font-black text-white tracking-widest">{{ $code }}</span>
                <button onclick="navigator.clipboard.writeText('{{ $code }}').then(() => { this.textContent = '✓'; setTimeout(() => this.textContent = '📋', 1200); })"
                        class="text-sm hover:scale-110 transition-transform" title="Copy code">📋</button>
            </div>
            <div class="text-[10px] text-gray-500 mt-0.5">Share it — friends add you instantly</div>
        </div>
        @endif
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

    {{-- Add friend --}}
    <div class="fr-card rounded-2xl p-5 mb-6">
        <h2 class="text-sm font-black text-white mb-3">+ Add a friend</h2>
        <form method="POST" action="{{ route('friends.request') }}" class="flex flex-col sm:flex-row gap-2">
            @csrf
            <input type="text" name="q" required maxlength="120" placeholder="@username, friend code (PQ-XXXXXX) or exact name…"
                   class="flex-1 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                   style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-black text-white transition-transform hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#6366f1,#a78bfa);box-shadow:0 4px 20px rgba(99,102,241,0.3);">
                <x-icon name="send" class="w-3.5 h-3.5 inline-block" /> Send Request
            </button>
        </form>

        @if($classmates->isNotEmpty())
        <div class="mt-4">
            <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 mb-2 inline-flex items-center gap-1"><x-icon name="graduation" class="w-3 h-3" /> Classmates you may know</div>
            <div class="flex flex-wrap gap-2">
                @foreach($classmates as $cm)
                <form method="POST" action="{{ route('friends.request') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $cm->id }}">
                    <button type="submit" class="fr-pill flex items-center gap-1.5">
                        @if($cm->avatar_url)<img src="{{ $cm->avatar_url }}" class="w-4 h-4 rounded-full object-cover" alt="" onerror="this.remove()">@endif
                        {{ $cm->name }} <span class="text-indigo-300">+</span>
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Requests --}}
    @if($incoming->isNotEmpty() || $outgoing->isNotEmpty())
    <div class="grid sm:grid-cols-2 gap-4 mb-6">
        <div class="fr-card rounded-2xl p-5">
            <h2 class="text-sm font-black text-white mb-3 inline-flex items-center gap-1"><x-icon name="inbox" class="w-3.5 h-3.5" /> Requests for you <span class="text-indigo-300">({{ $incoming->count() }})</span></h2>
            @forelse($incoming as $f)
            <div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
                <a href="{{ route('players.show', $f->requester) }}" class="flex items-center gap-2 flex-1 min-w-0 hover:opacity-80">
                    @if($f->requester->avatar_url)
                    <img src="{{ $f->requester->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt=""
                         onerror="this.outerHTML='<span class=\'w-8 h-8 rounded-full flex items-center justify-center text-xs font-black\' style=\'background:linear-gradient(135deg,#4f46e5,#a78bfa);\'>{{ strtoupper(substr($f->requester->name, 0, 1)) }}</span>'">
                    @else
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black" style="background:linear-gradient(135deg,#4f46e5,#a78bfa);">{{ strtoupper(substr($f->requester->name, 0, 1)) }}</span>
                    @endif
                    <span class="text-xs font-bold text-white truncate">{{ $f->requester->name }}
                        @if($f->requester->username)<span class="text-gray-500 font-semibold">{{ $f->requester->handle }}</span>@endif
                    </span>
                </a>
                <form method="POST" action="{{ route('friends.accept', $f) }}">@csrf
                    <button class="text-[11px] font-black px-3 py-1.5 rounded-lg text-emerald-300" style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.35);">Accept</button>
                </form>
                <form method="POST" action="{{ route('friends.decline', $f) }}">@csrf
                    <button class="text-[11px] font-bold px-3 py-1.5 rounded-lg text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">Decline</button>
                </form>
            </div>
            @empty
            <p class="text-xs text-gray-500">No pending requests.</p>
            @endforelse
        </div>
        <div class="fr-card rounded-2xl p-5">
            <h2 class="text-sm font-black text-white mb-3 inline-flex items-center gap-1"><x-icon name="send" class="w-3.5 h-3.5" /> Sent by you <span class="text-indigo-300">({{ $outgoing->count() }})</span></h2>
            @forelse($outgoing as $f)
            <div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
                <span class="text-xs font-bold text-white flex-1 truncate">{{ $f->addressee->name }}</span>
                <span class="text-[10px] text-gray-500">waiting…</span>
                <form method="POST" action="{{ route('friends.destroy', $f) }}">@csrf @method('DELETE')
                    <button class="text-[11px] font-bold px-3 py-1.5 rounded-lg text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">Cancel</button>
                </form>
            </div>
            @empty
            <p class="text-xs text-gray-500">Nothing waiting.</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Friends list --}}
    <div class="fr-card rounded-2xl p-5 mb-6">
        <h2 class="text-sm font-black text-white mb-3 inline-flex items-center gap-1"><x-icon name="people" class="w-3.5 h-3.5" /> My friends <span class="text-indigo-300">({{ $friends->count() }})</span></h2>
        @if($friends->isEmpty())
        <div class="text-center py-6">
            <p class="text-3xl mb-2">🫂</p>
            <p class="text-xs text-gray-400">No friends yet — share your code above, or add classmates.</p>
        </div>
        @else
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach($friends as $f)
            @php $friend = $f->otherUser($user->id); @endphp
            @if($friend)
            <div class="rounded-xl p-3 flex items-center gap-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                <a href="{{ route('players.show', $friend) }}" class="flex-shrink-0 hover:opacity-80">
                    @if($friend->avatar_url)
                    <img src="{{ $friend->avatar_url }}" class="w-11 h-11 rounded-full object-cover" style="box-shadow:0 0 0 2px rgba(99,102,241,0.35);" alt=""
                         onerror="this.outerHTML='<span class=\'w-11 h-11 rounded-full flex items-center justify-center text-sm font-black\' style=\'background:linear-gradient(135deg,#4f46e5,#a78bfa);box-shadow:0 0 0 2px rgba(99,102,241,0.35);display:flex;\'>{{ strtoupper(substr($friend->name, 0, 1)) }}</span>'">
                    @else
                    <span class="w-11 h-11 rounded-full flex items-center justify-center text-sm font-black" style="background:linear-gradient(135deg,#4f46e5,#a78bfa);box-shadow:0 0 0 2px rgba(99,102,241,0.35);display:flex;">{{ strtoupper(substr($friend->name, 0, 1)) }}</span>
                    @endif
                </a>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('players.show', $friend) }}" class="text-xs font-black text-white truncate block hover:text-indigo-300">{{ $friend->name }}</a>
                    <div class="text-[10px] text-gray-500">
                        @if($friend->username)<span class="text-indigo-300/80 font-bold">{{ $friend->handle }}</span> · @endif
                        Lvl {{ $friend->progress->level ?? 1 }}
                        @if($friend->progress)
                        · <span class="{{ ($friend->progress->credit_score ?? 500) >= 650 ? 'text-emerald-400' : (($friend->progress->credit_score ?? 500) >= 550 ? 'text-amber-400' : 'text-red-400') }}">{{ $friend->progress->creditScoreLabel() }} credit</span>
                        @endif
                    </div>
                </div>
                @if($giftsEnabled)
                    @if($sendMoneyAccess)
                    <button @click="openGiftModal({{ $friend->id }}, '{{ addslashes($friend->name) }}')"
                            class="text-[10px] font-black px-2.5 py-1.5 rounded-lg text-amber-300 flex-shrink-0" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);"
                            title="Send {{ $friend->name }} money">💰 Send</button>
                    @else
                    <a href="{{ route('subscribe.index') }}"
                       class="text-[10px] font-black px-2.5 py-1.5 rounded-lg text-gray-500 flex-shrink-0" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);"
                       title="Subscribe to send money to friends">🔒 Send</a>
                    @endif
                @endif
                @if($loansEnabled)
                <button @click="openLoanModal({{ $friend->id }}, '{{ addslashes($friend->name) }}')"
                        class="text-[10px] font-black px-2.5 py-1.5 rounded-lg text-emerald-300 flex-shrink-0" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);"
                        title="Ask {{ $friend->name }} for a loan">💸 Borrow</button>
                @endif
                <form method="POST" action="{{ route('friends.destroy', $f) }}" onsubmit="return confirm('Remove {{ addslashes($friend->name) }} from your friends?')">
                    @csrf @method('DELETE')
                    <button class="text-[10px] px-2 py-1.5 rounded-lg text-gray-500 hover:text-red-400 flex-shrink-0" title="Unfriend">✕</button>
                </form>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>

    {{-- Friend loans --}}
    @if($loansEnabled)
    <div class="fr-card rounded-2xl p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
            <h2 class="text-sm font-black text-white inline-flex items-center gap-1"><x-icon name="coin" class="w-3.5 h-3.5" /> Friend loans</h2>
            <span class="text-[10px] text-gray-500">Rates {{ implode('–', [min(\App\Models\FriendLoan::RATE_PRESETS), max(\App\Models\FriendLoan::RATE_PRESETS)]) }}% · repay in {{ implode('/', \App\Models\FriendLoan::TERM_PRESETS) }} game days · lenders risk max 20% of their cash</span>
        </div>
        <p class="text-[11px] text-gray-500 mb-4">You negotiate with choices, not chat: they ask → you offer a rate → they accept or counter once. Miss the due date and the game collects what it can — the rest becomes a default that wrecks your credit.</p>

        @if($myLoans->isEmpty())
        <p class="text-xs text-gray-500 text-center py-4">No loans yet. Tap 💸 Borrow on a friend to start a negotiation.</p>
        @else
        <div class="space-y-3">
            @foreach($myLoans as $loan)
            @php
                $iAmLender  = $loan->lender_id === $user->id;
                $other      = $iAmLender ? $loan->borrower : $loan->lender;
                $rate       = $loan->effectiveRate();
                $tick       = (int) ($progress->tick_count ?? 0);
                $dueIn      = $loan->due_at_tick !== null ? (int) $loan->due_at_tick - $tick : null;
            @endphp
            <div class="rounded-xl p-4" style="background:rgba(255,255,255,0.03);border:1px solid {{ $loan->status === 'active' ? 'rgba(16,185,129,0.3)' : ($loan->status === 'defaulted' ? 'rgba(239,68,68,0.3)' : 'rgba(255,255,255,0.08)') }};">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="text-lg">{{ $iAmLender ? '🏦' : '💸' }}</span>
                    <span class="text-xs font-black text-white">
                        {{ $iAmLender ? "Lending to {$other?->name}" : "Borrowing from {$other?->name}" }}
                        · Ksh {{ number_format($loan->amount) }}
                        @if($rate !== null) at {{ $rate }}% @endif
                        · {{ $loan->term_ticks }} game days
                    </span>
                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full ml-auto
                        {{ ['active' => 'text-emerald-300', 'repaid' => 'text-emerald-300', 'defaulted' => 'text-red-300'][$loan->status] ?? 'text-amber-300' }}"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);">
                        {{ $loan->statusLabel() }}
                    </span>
                </div>

                {{-- Lender: incoming request → offer a rate --}}
                @if($iAmLender && $loan->status === 'requested')
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] text-gray-400 mr-1">Offer interest:
                        @if($loan->borrower?->progress)
                        <span class="text-gray-500">(their credit: {{ $loan->borrower->progress->creditScoreLabel() }})</span>
                        @endif
                    </span>
                    @foreach(\App\Models\FriendLoan::RATE_PRESETS as $r)
                    <form method="POST" action="{{ route('friends.loans.offer', $loan) }}">@csrf
                        <input type="hidden" name="rate_pct" value="{{ $r }}">
                        <button class="fr-pill">{{ $r }}%</button>
                    </form>
                    @endforeach
                    <form method="POST" action="{{ route('friends.loans.decline', $loan) }}">@csrf
                        <button class="fr-pill" style="color:#f87171;">Decline</button>
                    </form>
                </div>

                {{-- Borrower: offer received → accept / counter / decline --}}
                @elseif(!$iAmLender && $loan->status === 'offered')
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] text-gray-400 mr-1">Repay Ksh {{ number_format((int) round($loan->amount * (1 + $loan->rate_pct / 100))) }} total:</span>
                    <form method="POST" action="{{ route('friends.loans.accept', $loan) }}">@csrf
                        <button class="fr-pill" style="background:rgba(16,185,129,0.15);border-color:rgba(16,185,129,0.4);color:#6ee7b7;">✓ Accept {{ $loan->rate_pct }}%</button>
                    </form>
                    @if($loan->counter_rate_pct === null)
                    @foreach(\App\Models\FriendLoan::RATE_PRESETS as $r)
                    @if($r < $loan->rate_pct)
                    <form method="POST" action="{{ route('friends.loans.counter', $loan) }}">@csrf
                        <input type="hidden" name="rate_pct" value="{{ $r }}">
                        <button class="fr-pill" title="One counter-offer only!">↩ Counter {{ $r }}%</button>
                    </form>
                    @endif
                    @endforeach
                    @endif
                    <form method="POST" action="{{ route('friends.loans.decline', $loan) }}">@csrf
                        <button class="fr-pill" style="color:#f87171;">Decline</button>
                    </form>
                </div>

                {{-- Lender: counter received → accept / decline --}}
                @elseif($iAmLender && $loan->status === 'countered')
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] text-gray-400 mr-1">They countered {{ $loan->rate_pct }}% → {{ $loan->counter_rate_pct }}%:</span>
                    <form method="POST" action="{{ route('friends.loans.accept', $loan) }}">@csrf
                        <button class="fr-pill" style="background:rgba(16,185,129,0.15);border-color:rgba(16,185,129,0.4);color:#6ee7b7;">✓ Accept {{ $loan->counter_rate_pct }}%</button>
                    </form>
                    <form method="POST" action="{{ route('friends.loans.decline', $loan) }}">@csrf
                        <button class="fr-pill" style="color:#f87171;">Decline</button>
                    </form>
                </div>

                {{-- Active loan --}}
                @elseif($loan->status === 'active')
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] {{ !$iAmLender && $dueIn !== null && $dueIn <= 5 ? 'text-amber-300 font-bold' : 'text-gray-400' }}">
                        @if($iAmLender)
                        They owe you Ksh {{ number_format($loan->remaining()) }}@if($dueIn !== null) · due in ~{{ max(0, $dueIn) }} of their game days @endif
                        @else
                        You owe Ksh {{ number_format($loan->remaining()) }}@if($dueIn !== null) · due in {{ max(0, $dueIn) }} game day{{ $dueIn === 1 ? '' : 's' }}@endif
                        @if($dueIn !== null && $dueIn <= 0) · ⚠️ OVERDUE — auto-collection is coming @endif
                        @endif
                    </span>
                    @if(!$iAmLender)
                    <form method="POST" action="{{ route('friends.loans.repay', $loan) }}" class="ml-auto">@csrf
                        <button class="fr-pill" style="background:rgba(16,185,129,0.15);border-color:rgba(16,185,129,0.4);color:#6ee7b7;">💰 Repay Ksh {{ number_format($loan->remaining()) }}</button>
                    </form>
                    @endif
                </div>

                {{-- Waiting / closed states --}}
                @elseif(in_array($loan->status, ['requested', 'offered', 'countered'], true))
                <p class="text-[11px] text-gray-500">Waiting for {{ $other?->name }} to respond… (negotiations expire after 3 days)</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif
</div>

{{-- ── Borrow modal ── --}}
@if($loansEnabled)
<div x-cloak x-show="loanOpen" @click.self="loanOpen = false"
     class="fixed inset-0 z-[9995] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);">
    <div class="rounded-2xl p-4 sm:p-6 w-full max-w-md" style="background:#100e1e;border:1px solid rgba(99,102,241,0.35);">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-base font-black text-white inline-flex items-center gap-1"><x-icon name="coin" class="w-4 h-4" /> Borrow from <span x-text="loanFriendName" class="text-indigo-300"></span></h3>
            <button @click="loanOpen = false" class="text-gray-400 hover:text-white text-lg sm:text-xl">✕</button>
        </div>
        <p class="text-[11px] text-gray-500 mb-4">Pick an amount and repayment period. Your friend then offers an interest rate ({{ implode('–', [min(\App\Models\FriendLoan::RATE_PRESETS), max(\App\Models\FriendLoan::RATE_PRESETS)]) }}%) which you can accept or counter once.</p>
        <form method="POST" action="{{ route('friends.loans.request') }}">
            @csrf
            <input type="hidden" name="lender_id" :value="loanFriendId">
            <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 mb-2">Amount (Ksh)</div>
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach(\App\Models\FriendLoan::AMOUNT_PRESETS as $amt)
                <label class="fr-pill" :class="{ 'on': loanAmount === {{ $amt }} }">
                    <input type="radio" name="amount" value="{{ $amt }}" x-model.number="loanAmount" class="hidden" required>
                    {{ number_format($amt) }}
                </label>
                @endforeach
            </div>
            <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 mb-2">Repay within (game days)</div>
            <div class="flex flex-wrap gap-2 mb-5">
                @foreach(\App\Models\FriendLoan::TERM_PRESETS as $t)
                <label class="fr-pill" :class="{ 'on': loanTerm === {{ $t }} }">
                    <input type="radio" name="term_ticks" value="{{ $t }}" x-model.number="loanTerm" class="hidden" required>
                    {{ $t }} days
                </label>
                @endforeach
            </div>
            <button type="submit" :disabled="!loanAmount || !loanTerm"
                    class="w-full py-3 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.01] disabled:opacity-40 disabled:cursor-not-allowed"
                    style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 20px rgba(16,185,129,0.3);">
                Send loan request →
            </button>
        </form>
    </div>
</div>
@endif

{{-- ── Send Money modal ── --}}
@if($giftsEnabled && $sendMoneyAccess)
<div x-cloak x-show="giftOpen" @click.self="giftOpen = false"
     class="fixed inset-0 z-[9995] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);">
    <div class="rounded-2xl p-4 sm:p-6 w-full max-w-md" style="background:#100e1e;border:1px solid rgba(245,158,11,0.35);">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-base font-black text-white inline-flex items-center gap-1"><x-icon name="coin" class="w-4 h-4" /> Send money to <span x-text="giftFriendName" class="text-amber-300"></span></h3>
            <button @click="giftOpen = false" class="text-gray-400 hover:text-white text-lg sm:text-xl">✕</button>
        </div>
        <p class="text-[11px] text-gray-500 mb-4">An instant gift, straight from your balance — no interest, no repayment. Up to {{ \App\Models\FriendGift::DAILY_LIMIT }} gifts a day, max 20% of your cash each time.</p>
        <form method="POST" action="{{ route('friends.gift') }}">
            @csrf
            <input type="hidden" name="friend_id" :value="giftFriendId">
            <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 mb-2">Amount (Ksh)</div>
            <input type="number" name="amount" x-model.number="giftAmount" min="10" max="50000" step="10" required
                   placeholder="e.g. 500" class="w-full rounded-xl px-3 py-2.5 text-sm font-bold text-white mb-4"
                   style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.15);">
            <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 mb-2">Message (optional)</div>
            <input type="text" name="message" x-model="giftMessage" maxlength="120"
                   placeholder="e.g. Happy level up! 🎉" class="w-full rounded-xl px-3 py-2.5 text-sm text-white mb-5"
                   style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.15);">
            <button type="submit" :disabled="!giftAmount || giftAmount < 10"
                    class="w-full py-3 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.01] disabled:opacity-40 disabled:cursor-not-allowed"
                    style="background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 20px rgba(245,158,11,0.3);">
                Send money →
            </button>
        </form>
    </div>
</div>
@endif

<x-mobile-bottom-nav active="people" />
</body>
</html>
