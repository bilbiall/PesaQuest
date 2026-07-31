<?php

namespace App\Http\Controllers;

use App\Models\FriendLoan;
use App\Models\Friendship;
use App\Models\GameNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Structured P2P loan negotiation — players never type messages at each
 * other, they pick from presets:
 *   borrower: amount + term  →  lender: rate (or decline)
 *   borrower: accept | ONE counter-rate | decline  →  lender: accept | decline
 * Money only moves at activation, on the borrower's clock.
 */
class FriendLoanController extends Controller
{
    public function request(Request $request)
    {
        abort_unless(Schema::hasTable('friend_loans'), 503);
        $user = auth()->user();

        $data = $request->validate([
            'lender_id'  => 'required|integer|exists:users,id',
            'amount'     => 'required|integer',
            'term_ticks' => 'required|integer',
        ]);

        if (!in_array((int) $data['amount'], FriendLoan::AMOUNT_PRESETS, true) ||
            !in_array((int) $data['term_ticks'], FriendLoan::TERM_PRESETS, true)) {
            return back()->with('error', 'Pick an amount and repayment period from the options.');
        }

        $lender = User::findOrFail((int) $data['lender_id']);
        if ($err = $this->gateBoth($user, $lender)) return back()->with('error', $err);

        if (FriendLoan::openCountFor($user->id, 'borrower_id') >= FriendLoan::MAX_OPEN_PER_SIDE) {
            return back()->with('error', 'You already have ' . FriendLoan::MAX_OPEN_PER_SIDE . ' open loans as a borrower — settle one first.');
        }
        if (FriendLoan::whereIn('status', ['requested', 'offered', 'countered', 'active'])
            ->where('borrower_id', $user->id)->where('lender_id', $lender->id)->exists()) {
            return back()->with('error', "You already have an open loan with {$lender->name}.");
        }

        $loan = FriendLoan::create([
            'lender_id'   => $lender->id,
            'borrower_id' => $user->id,
            'amount'      => (int) $data['amount'],
            'term_ticks'  => (int) $data['term_ticks'],
            'status'      => 'requested',
            'negotiation_expires_at' => now()->addDays(3),
        ]);

        $this->notify($lender->id, 'friend_loan', '🤝 ' . $user->name . ' asked to borrow Ksh ' . number_format($loan->amount),
            'Repaying over ' . $loan->term_ticks . ' game days. Open the Friends page to offer an interest rate or decline.');

        return back()->with('success', "Loan request sent to {$lender->name} — they'll offer a rate.");
    }

    /** Lender proposes an interest rate from the presets. */
    public function offer(Request $request, FriendLoan $loan)
    {
        $user = auth()->user();
        abort_unless($loan->lender_id === $user->id && $loan->status === 'requested', 403);
        if ($this->expireIfStale($loan)) return back()->with('error', 'This request expired.');

        $rate = (int) $request->validate(['rate_pct' => 'required|integer'])['rate_pct'];
        if (!in_array($rate, FriendLoan::RATE_PRESETS, true)) {
            return back()->with('error', 'Pick a rate from the options.');
        }

        $loan->update(['rate_pct' => $rate, 'status' => 'offered', 'negotiation_expires_at' => now()->addDays(3)]);

        $repay = (int) round($loan->amount * (1 + $rate / 100));
        $this->notify($loan->borrower_id, 'friend_loan', '💬 ' . $user->name . ' offered ' . $rate . '% interest',
            'Borrow Ksh ' . number_format($loan->amount) . ', repay Ksh ' . number_format($repay) . ' in ' . $loan->term_ticks . ' game days. Accept, counter once, or decline.');

        return back()->with('success', 'Offer sent — ' . $rate . '% interest.');
    }

    /** Borrower's single counter-offer at a lower preset rate. */
    public function counter(Request $request, FriendLoan $loan)
    {
        $user = auth()->user();
        abort_unless($loan->borrower_id === $user->id && $loan->status === 'offered', 403);
        if ($this->expireIfStale($loan)) return back()->with('error', 'This offer expired.');
        if ($loan->counter_rate_pct !== null) return back()->with('error', 'You already used your one counter-offer.');

        $rate = (int) $request->validate(['rate_pct' => 'required|integer'])['rate_pct'];
        if (!in_array($rate, FriendLoan::RATE_PRESETS, true) || $rate >= (int) $loan->rate_pct) {
            return back()->with('error', 'A counter-offer must be a preset rate lower than what was offered.');
        }

        $loan->update(['counter_rate_pct' => $rate, 'status' => 'countered', 'negotiation_expires_at' => now()->addDays(3)]);

        $this->notify($loan->lender_id, 'friend_loan', '↩️ ' . $user->name . ' countered at ' . $rate . '%',
            'They want Ksh ' . number_format($loan->amount) . ' at ' . $rate . '% instead of ' . $loan->rate_pct . '%. Accept or decline on the Friends page.');

        return back()->with('success', 'Counter-offer sent — ' . $rate . '%.');
    }

    /**
     * Accept — borrower accepts an offer, or lender accepts a counter.
     * This is the moment money moves.
     */
    public function accept(FriendLoan $loan)
    {
        $user = auth()->user();

        $isBorrowerAccepting = $loan->borrower_id === $user->id && $loan->status === 'offered';
        $isLenderAccepting   = $loan->lender_id === $user->id && $loan->status === 'countered';
        abort_unless($isBorrowerAccepting || $isLenderAccepting, 403);
        if ($this->expireIfStale($loan)) return back()->with('error', 'This negotiation expired.');

        $lender   = $loan->lender()->first();
        $borrower = $loan->borrower()->first();
        if ($err = $this->gateBoth($borrower, $lender)) return back()->with('error', $err);

        $rate = (int) $loan->effectiveRate();

        $lenderProgress   = $lender->getOrCreateProgress();
        $borrowerProgress = $borrower->getOrCreateProgress();

        $maxLend = (int) floor(($lenderProgress->balance ?? 0) * FriendLoan::MAX_LEND_SHARE);
        if ($loan->amount > $maxLend) {
            return back()->with('error', $loan->lender_id === $user->id
                ? 'You can lend at most 20% of your cash (Ksh ' . number_format($maxLend) . ' right now).'
                : $lender->name . ' cannot cover this loan right now (lenders may commit at most 20% of their cash).');
        }

        DB::transaction(function () use ($loan, $rate, $lenderProgress, $borrowerProgress) {
            $lenderProgress->balance -= $loan->amount;
            $lenderProgress->recalculateNetWorth();
            $lenderProgress->save();

            $borrowerProgress->balance += $loan->amount;
            $borrowerProgress->recalculateNetWorth();
            $borrowerProgress->save();

            $tick = (int) ($borrowerProgress->tick_count ?? 0);
            $loan->update([
                'status'            => 'active',
                'rate_pct'          => $rate,
                'counter_rate_pct'  => null,
                'total_due'         => (int) round($loan->amount * (1 + $rate / 100)),
                'disbursed_at_tick' => $tick,
                'due_at_tick'       => $tick + $loan->term_ticks,
                'negotiation_expires_at' => null,
            ]);
        });

        $this->notify($loan->borrower_id, 'friend_loan', '💸 Loan agreed at ' . $rate . '% — Ksh ' . number_format($loan->amount) . ' received',
            'Repay Ksh ' . number_format($loan->total_due) . ' within ' . $loan->term_ticks . ' game days (it\'s on your calendar). Late payment hurts your credit AND your friend.');
        $this->notify($loan->lender_id, 'friend_loan', '🤝 You lent Ksh ' . number_format($loan->amount) . ' to ' . $borrower->name,
            'They owe you Ksh ' . number_format($loan->total_due) . ' at ' . $rate . '% within ' . $loan->term_ticks . ' of their game days.');

        return back()->with('success', 'Deal! The loan is now active.');
    }

    /** Either side can walk away from an open negotiation. */
    public function decline(FriendLoan $loan)
    {
        $user = auth()->user();
        abort_unless(in_array($user->id, [$loan->lender_id, $loan->borrower_id], true), 403);
        abort_unless(in_array($loan->status, ['requested', 'offered', 'countered'], true), 403);

        $loan->update(['status' => 'declined', 'negotiation_expires_at' => null]);

        $other = $user->id === $loan->lender_id ? $loan->borrower_id : $loan->lender_id;
        $this->notify($other, 'friend_loan', '🚫 ' . $user->name . ' declined the loan negotiation',
            'No hard feelings — you can always start a new request.');

        return back()->with('success', 'Negotiation declined.');
    }

    /** Borrower repays the full remaining amount, manually, any time before (or after) due. */
    public function repay(FriendLoan $loan)
    {
        $user = auth()->user();
        abort_unless($loan->borrower_id === $user->id && $loan->status === 'active', 403);

        $progress  = $user->getOrCreateProgress();
        $remaining = $loan->remaining();

        if (($progress->balance ?? 0) < $remaining) {
            return back()->with('error', 'You need Ksh ' . number_format($remaining) . ' to clear this loan — you have Ksh ' . number_format($progress->balance) . '.');
        }

        $lenderProgress = $loan->lender->getOrCreateProgress();

        DB::transaction(function () use ($loan, $progress, $lenderProgress, $remaining) {
            $progress->balance -= $remaining;
            $lenderProgress->balance += $remaining;
            $lenderProgress->recalculateNetWorth();
            $lenderProgress->save();

            $onTime = $loan->due_at_tick === null || (int) ($progress->tick_count ?? 0) <= (int) $loan->due_at_tick;
            $progress->adjustCreditScoreWithLog($onTime ? 10 : 3,
                ($onTime ? 'Repaid a friend loan on time' : 'Repaid a friend loan (late)') . ' — ' . $loan->lender->name,
                ['kind' => 'friend_loan_repaid']);
            $progress->recalculateNetWorth();
            $progress->save();

            $loan->update(['status' => 'repaid', 'amount_repaid' => (int) $loan->total_due]);
        });

        $this->notify($loan->lender_id, 'friend_loan', '💰 ' . $user->name . ' repaid your loan in full',
            'Ksh ' . number_format($remaining) . ' has landed in your wallet. Lending to reliable friends pays.');

        return back()->with('success', 'Loan cleared — Ksh ' . number_format($remaining) . ' repaid. Your credit thanks you.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Shared eligibility rules for both parties. Returns an error string or null. */
    private function gateBoth(User $borrower, User $lender): ?string
    {
        if (!Friendship::areFriends($borrower->id, $lender->id)) {
            return 'You can only borrow from accepted friends.';
        }

        $bLevel = (int) ($borrower->getOrCreateProgress()->level ?? 1);
        $lLevel = (int) ($lender->getOrCreateProgress()->level ?? 1);
        if ($bLevel < FriendLoan::MIN_LEVEL || $lLevel < FriendLoan::MIN_LEVEL) {
            return 'Both players must be level ' . FriendLoan::MIN_LEVEL . '+ to use friend loans.';
        }

        if (FriendLoan::openCountFor($lender->id, 'lender_id') >= FriendLoan::MAX_OPEN_PER_SIDE) {
            return $lender->name . ' already has ' . FriendLoan::MAX_OPEN_PER_SIDE . ' open loans as a lender.';
        }

        return null;
    }

    /** Negotiations rot after 3 real days so nobody is left hanging. */
    private function expireIfStale(FriendLoan $loan): bool
    {
        if ($loan->negotiation_expires_at && $loan->negotiation_expires_at->isPast()
            && in_array($loan->status, ['requested', 'offered', 'countered'], true)) {
            $loan->update(['status' => 'expired', 'negotiation_expires_at' => null]);
            return true;
        }
        return false;
    }

    private function notify(int $userId, string $type, string $title, string $body): void
    {
        GameNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'icon'    => '🤝',
            'data'    => ['url' => '/friends'],
        ]);
    }
}
