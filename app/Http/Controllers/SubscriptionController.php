<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\MpesaTransaction;
use App\Models\SubscriptionPlan;
use App\Services\MpesaService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans          = SubscriptionPlan::where('is_active', true)->orderBy('months')->get();
        $individualPlans = $plans->where('plan_type', 'individual')->values();
        $schoolPlans     = $plans->where('plan_type', 'school')->sortBy('seats')->values();
        $user           = auth()->user();
        $activeSub      = $user->activeSubscription();
        $upcomingSub    = $user->upcomingSubscription();

        // Fetch transaction history for the current user (most recent 20)
        $transactions = MpesaTransaction::where('user_id', $user->id)
            ->with('plan')
            ->latest()
            ->take(20)
            ->get();

        return view('subscription.index', compact('plans', 'individualPlans', 'schoolPlans', 'user', 'activeSub', 'upcomingSub', 'transactions'));
    }

    public function pay(Request $request, SubscriptionPlan $plan)
    {
        if (!$plan->is_active) {
            return response()->json(['error' => 'This plan is not currently available.'], 422);
        }

        $rules = [
            'phone'  => ['required', 'string', 'regex:/^(\+?254|0)(7|1)\d{8}$/'],
            'coupon' => ['nullable', 'string', 'max:30'],
        ];
        $messages = ['phone.regex' => 'Enter a valid Kenyan phone number (07XXXXXXXX or 254XXXXXXXXX).'];

        if ($plan->isSchool()) {
            $rules['school_name'] = ['required', 'string', 'min:3', 'max:255'];
            $messages['school_name.required'] = 'Please enter your school name.';
        }

        $validated = $request->validate($rules, $messages);

        // Resolve + validate the coupon (if given) — invalid codes block payment
        $coupon = null;
        if (!empty($validated['coupon'])) {
            $coupon = Coupon::findByCode($validated['coupon']);
            if (!$coupon) {
                return response()->json(['error' => 'Unknown coupon code.'], 422);
            }
            if ($reason = $coupon->invalidReason($plan)) {
                return response()->json(['error' => $reason], 422);
            }
        }

        $schoolName = $plan->isSchool() ? $validated['school_name'] : null;

        try {
            // 100%-off coupon: activate immediately, no M-Pesa prompt
            if ($coupon && $coupon->discountFor((int) $plan->price_kes) >= (int) $plan->price_kes) {
                $txn = app(MpesaService::class)->activateFree(auth()->user(), $plan, $coupon, $schoolName);

                return response()->json([
                    'success'             => true,
                    'free'                => true,
                    'message'             => "Coupon applied — your {$plan->name} subscription is active! 🎉",
                    'checkout_request_id' => $txn->checkout_request_id,
                ]);
            }

            $txn = app(MpesaService::class)->stkPush(auth()->user(), $plan, $validated['phone'], $schoolName, $coupon);

            return response()->json([
                'success'             => true,
                'message'             => 'M-Pesa prompt sent! Enter your PIN on your phone.',
                'checkout_request_id' => $txn->checkout_request_id,
                'amount'              => $txn->amount,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /** Live coupon validation for the subscribe modal — returns discount + new total. */
    public function couponCheck(Request $request, SubscriptionPlan $plan)
    {
        $coupon = Coupon::findByCode($request->input('code', ''));

        if (!$coupon) {
            return response()->json(['valid' => false, 'reason' => 'Unknown coupon code.']);
        }
        if ($reason = $coupon->invalidReason($plan)) {
            return response()->json(['valid' => false, 'reason' => $reason]);
        }

        $discount = $coupon->discountFor((int) $plan->price_kes);

        return response()->json([
            'valid'    => true,
            'label'    => $coupon->label(),
            'discount' => $discount,
            'total'    => max(0, $plan->price_kes - $discount),
        ]);
    }

    public function status(Request $request)
    {
        $txn = MpesaTransaction::where('checkout_request_id', $request->checkout_request_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$txn) {
            return response()->json(['status' => 'unknown']);
        }

        $response = [
            'status'  => $txn->status,
            'receipt' => $txn->mpesa_receipt,
            'reason'  => $txn->failure_reason,
        ];

        // For school plans, surface the portal URL once payment completes
        if ($txn->status === 'completed' && $txn->plan?->isSchool()) {
            $school = \App\Models\SchoolSubscription::where('created_by', auth()->id())
                ->where('created_at', '>=', $txn->completed_at?->subMinutes(2) ?? now()->subMinutes(5))
                ->latest()
                ->first();

            if ($school) {
                $response['portal_url'] = route('school.portal', $school->portal_token);
            }
        }

        return response()->json($response);
    }
}
