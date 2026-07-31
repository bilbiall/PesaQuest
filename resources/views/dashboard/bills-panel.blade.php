{{-- ══════════════════════════════════════════════════════════
     BILLS & CREDIT PANEL
     Shows: credit score, monthly burn, overdue + upcoming bills
     ══════════════════════════════════════════════════════════ --}}

@php
    $currentTick = $progress->tick_count ?? 0;
    $score       = $progress->credit_score ?? 500;
    $scoreLabel  = $progress->creditScoreLabel();
    $scoreColor  = match(true) {
        $score >= 750 => ['text' => 'text-emerald-400', 'bar' => '#10b981', 'bg' => 'rgba(16,185,129,0.15)', 'border' => 'rgba(16,185,129,0.3)'],
        $score >= 650 => ['text' => 'text-blue-400',    'bar' => '#60a5fa', 'bg' => 'rgba(96,165,250,0.15)', 'border' => 'rgba(96,165,250,0.3)'],
        $score >= 550 => ['text' => 'text-amber-400',   'bar' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.15)', 'border' => 'rgba(245,158,11,0.3)'],
        $score >= 450 => ['text' => 'text-orange-400',  'bar' => '#fb923c', 'bg' => 'rgba(251,146,60,0.15)', 'border' => 'rgba(251,146,60,0.3)'],
        default       => ['text' => 'text-red-400',     'bar' => '#f87171', 'bg' => 'rgba(248,113,113,0.15)','border' => 'rgba(248,113,113,0.3)'],
    };
    $scorePct = round(($score - 300) / 550 * 100);
    $hasOverdue  = $overdueBills->count() > 0;
@endphp

<div class="rounded-3xl overflow-hidden" style="background:linear-gradient(160deg,rgba(15,23,42,0.9),rgba(30,27,75,0.7));border:1px solid {{ $hasOverdue ? 'rgba(248,113,113,0.4)' : 'rgba(255,255,255,0.07)' }};"
     x-data="billsPanel()" x-init="init()">

    {{-- Header --}}
    <div class="px-5 py-4 flex items-center justify-between" style="border-bottom:1px solid rgba(255,255,255,0.05);">
        <div class="flex items-center gap-2">
            <span class="text-lg">💳</span>
            <span class="font-black text-white text-sm">Bills & Credit</span>
            @if($hasOverdue)
            <span class="bg-red-500/25 text-red-400 text-xs font-black px-2 py-0.5 rounded-full animate-pulse">
                {{ $overdueBills->count() }} OVERDUE
            </span>
            @endif
        </div>
        <span class="text-xs text-gray-500">Ksh {{ number_format($monthlyBurn) }}/mo</span>
    </div>

    <div class="p-5 space-y-4">

        {{-- Credit Score --}}
        <div class="rounded-2xl p-4" style="background:{{ $scoreColor['bg'] }};border:1px solid {{ $scoreColor['border'] }};">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Credit Score</p>
                    <p class="text-2xl font-black {{ $scoreColor['text'] }}">{{ $score }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-black {{ $scoreColor['text'] }}">{{ $scoreLabel }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">300 — 850</p>
                </div>
            </div>
            <div class="h-2 rounded-full" style="background:rgba(255,255,255,0.08);">
                <div class="h-2 rounded-full transition-all duration-700" style="width:{{ $scorePct }}%;background:{{ $scoreColor['bar'] }};"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                @if($score >= 750) Your credit is excellent. Banks will love you.
                @elseif($score >= 650) Good standing. Keep paying bills on time.
                @elseif($score >= 550) Fair. A few missed payments pulling you down.
                @elseif($score >= 450) Poor. Overdue bills are hurting your score.
                @else Danger zone. Missed bills compounding — act fast.
                @endif
            </p>
        </div>

        {{-- Overdue Bills --}}
        @if($hasOverdue)
        <div>
            <p class="text-xs font-black text-red-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                <span>⚠️</span> Overdue — Pay Now
            </p>
            <div class="space-y-2">
                @foreach($overdueBills as $pb)
                <div class="rounded-2xl p-3 flex items-center gap-3" style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.3);"
                     id="bill-row-{{ $pb->id }}">
                    <span class="text-xl flex-shrink-0">{{ $pb->bill->icon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ $pb->bill->name }}</p>
                        <p class="text-xs text-red-400">
                            {{ $pb->missed_count }} missed · Ksh {{ number_format($pb->amount) }}
                        </p>
                    </div>
                    <button
                        @click="payBill({{ $pb->id }}, {{ $pb->amount }}, $el)"
                        :disabled="paying === {{ $pb->id }}"
                        class="px-3 py-1.5 rounded-xl text-xs font-black transition-all flex-shrink-0"
                        style="background:rgba(248,113,113,0.2);border:1px solid rgba(248,113,113,0.4);color:#fca5a5;"
                    >
                        <span x-show="paying !== {{ $pb->id }}">Pay Ksh {{ number_format($pb->amount) }}</span>
                        <span x-show="paying === {{ $pb->id }}">Paying…</span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Upcoming Bills --}}
        @if($upcomingBills->count() > 0)
        <div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Upcoming</p>
            <div class="space-y-2">
                @foreach($upcomingBills as $pb)
                @php
                    $ticksLeft  = $pb->next_due_tick - $currentTick;
                    $urgency    = $pb->urgencyClass($currentTick);
                    $urgColor   = match($urgency) {
                        'urgent' => 'text-red-400',
                        'soon'   => 'text-amber-400',
                        default  => 'text-gray-400',
                    };
                    $dueLabel = match(true) {
                        $ticksLeft <= 1  => 'Due tomorrow',
                        $ticksLeft <= 7  => "Due in {$ticksLeft} game days",
                        $ticksLeft <= 30 => 'Due this game month',
                        default          => 'Due next game month',
                    };
                @endphp
                <div class="rounded-xl p-3 flex items-center gap-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                    <span class="text-lg flex-shrink-0">{{ $pb->bill->icon }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white truncate">{{ $pb->bill->name }}</p>
                        <p class="text-xs {{ $urgColor }}">{{ $dueLabel }}</p>
                    </div>
                    <span class="text-xs font-black text-gray-300 flex-shrink-0">Ksh {{ number_format($pb->amount) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Empty state --}}
        @if($overdueBills->count() === 0 && $upcomingBills->count() === 0)
        <p class="text-center text-gray-500 text-sm py-3">No bills assigned yet. Start playing to unlock your life expenses.</p>
        @endif

        {{-- Feedback toast --}}
        <div x-show="msg" x-cloak x-transition class="rounded-xl px-4 py-2 text-xs font-bold text-center"
             :class="msgOk ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20' : 'text-red-400 bg-red-500/10 border border-red-500/20'"
             x-text="msg"></div>
    </div>
</div>

<script>
function billsPanel() {
    return {
        paying: null,
        msg: '', msgOk: true,
        init() {},
        async payBill(billId, amount, btn) {
            if (this.paying) return;
            this.paying = billId; this.msg = '';
            try {
                const res = await fetch(`/life/bills/${billId}/pay`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok) {
                    this.msgOk = true;
                    this.msg = `Paid! Balance: Ksh ${data.new_balance.toLocaleString()} · Credit: ${data.credit_score} (${data.credit_label})`;
                    // Remove the row from DOM
                    const row = document.getElementById(`bill-row-${billId}`);
                    if (row) row.remove();
                    // Update balance display if present
                    document.querySelectorAll('[data-balance]').forEach(el => el.textContent = `Ksh ${data.new_balance.toLocaleString()}`);
                } else {
                    this.msgOk = false;
                    this.msg = data.error || 'Payment failed.';
                }
            } catch { this.msgOk = false; this.msg = 'Network error.'; }
            this.paying = null;
        }
    };
}
</script>
