<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use App\Models\PlayerLoan;
use App\Services\PlanGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function take(Request $request)
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        $request->validate([
            'loan_product_id' => 'required|integer|exists:loan_products,id',
            'amount'          => 'required|integer|min:1',
        ]);

        $product = LoanProduct::where('id', $request->loan_product_id)
            ->where('is_active', true)
            ->firstOrFail();

        $amount = (int) $request->amount;
        $creditScore = $progress->credit_score ?? 500;

        if ($creditScore < $product->min_credit_score) {
            return response()->json([
                'error' => "Your credit score ({$creditScore}) is below the minimum ({$product->min_credit_score}) for this loan."
            ], 422);
        }

        if ($amount < $product->min_amount || $amount > $product->max_amount) {
            return response()->json([
                'error' => "Amount must be between KES " . number_format($product->min_amount) . " and KES " . number_format($product->max_amount) . "."
            ], 422);
        }

        // Max 2 active loans at a time (absolute game ceiling)
        $activeLoans = PlayerLoan::where('user_id', $user->id)->where('status', 'active')->count();
        if ($activeLoans >= 2) {
            return response()->json(['error' => 'You can only have 2 active loans at a time. Repay an existing loan first.'], 422);
        }

        // Plan gate: free accounts hold fewer loans (free = 1)
        $gate = app(PlanGate::class);
        if (!$gate->allows($user, 'max_loans', $activeLoans)) {
            return response()->json($gate->deny('max_loans', $gate->limit($user, 'max_loans')), 422);
        }

        $paymentAmount = $product->calculatePayment($amount);
        $tickCount     = $progress->tick_count ?? 0;

        DB::transaction(function () use ($user, $progress, $product, $amount, $paymentAmount, $tickCount) {
            $progress->balance += $amount;
            $progress->adjustCreditScoreWithLog(-5, 'Took on new debt: ' . $product->name, ['kind' => 'loan_taken']); // Small initial hit

            PlayerLoan::create([
                'user_id'              => $user->id,
                'loan_product_id'      => $product->id,
                'principal'            => $amount,
                'annual_interest_rate' => $product->annual_interest_rate,
                'outstanding_balance'  => $amount,
                'payment_amount'       => $paymentAmount,
                'payment_period_ticks' => $product->payment_period_ticks,
                'disbursed_at_tick'    => $tickCount,
                'due_at_tick'          => $tickCount + $product->term_ticks,
                'next_payment_tick'    => $tickCount + $product->payment_period_ticks,
                'status'               => 'active',
            ]);

            // Cash up, debt up — net worth unchanged in truth; keep cache accurate
            $progress->recalculateNetWorth();
            $progress->save();
        });

        return response()->json([
            'success'          => true,
            'message'          => "KES " . number_format($amount) . " disbursed to your account!",
            'balance'          => $progress->balance,
            'payment_amount'   => $paymentAmount,
            'payment_period'   => $product->payment_period_ticks,
        ]);
    }

    public function repay(Request $request, PlayerLoan $loan)
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        if ($loan->user_id !== $user->id) {
            return response()->json(['error' => 'Not your loan.'], 403);
        }
        if ($loan->status !== 'active') {
            return response()->json(['error' => 'This loan is not active.'], 422);
        }

        $request->validate(['amount' => 'required|integer|min:1']);

        $amount = min((int) $request->amount, $loan->outstanding_balance);

        if ($progress->balance < $amount) {
            return response()->json(['error' => "Insufficient balance. You have KES " . number_format($progress->balance) . "."], 422);
        }

        DB::transaction(function () use ($progress, $loan, $amount) {
            $progress->balance -= $amount;
            $loan->outstanding_balance = max(0, $loan->outstanding_balance - $amount);
            $loan->payments_made++;
            $progress->adjustCreditScoreWithLog(+5, 'Loan payment made', ['kind' => 'loan_paid', 'loan_id' => $loan->id]);

            if ($loan->outstanding_balance <= 0) {
                $loan->status = 'paid';
                $progress->adjustCreditScoreWithLog(+30, 'Loan fully paid off', ['kind' => 'loan_cleared', 'loan_id' => $loan->id]);
            }

            $loan->next_payment_tick = ($progress->tick_count ?? 0) + $loan->payment_period_ticks;
            $loan->save();

            $progress->recalculateNetWorth();
            $progress->save();
        });

        $message = $loan->status === 'paid'
            ? "Loan fully paid off! +35 credit score."
            : "Payment of KES " . number_format($amount) . " made. Balance: KES " . number_format($loan->outstanding_balance);

        return response()->json([
            'success'             => true,
            'message'             => $message,
            'balance'             => $progress->balance,
            'outstanding_balance' => $loan->outstanding_balance,
            'loan_status'         => $loan->status,
            'credit_score'        => $progress->credit_score,
        ]);
    }
}
