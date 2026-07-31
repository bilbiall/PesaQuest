<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\MpesaTransaction;
use App\Models\SchoolSubscription;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MpesaService
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private string $baseUrl;
    private string $callbackUrl;
    private string $accountRef;

    public function __construct()
    {
        $this->consumerKey    = Setting::get('mpesa_consumer_key',    env('MPESA_CONSUMER_KEY', ''));
        $this->consumerSecret = Setting::get('mpesa_consumer_secret', env('MPESA_CONSUMER_SECRET', ''));
        $this->shortcode      = Setting::get('mpesa_shortcode',       env('MPESA_SHORTCODE', '174379'));
        $this->passkey        = Setting::get('mpesa_passkey',         env('MPESA_PASSKEY', ''));
        $this->accountRef     = Setting::get('mpesa_account_ref',     env('MPESA_ACCOUNT_REF', 'PesaQuest'));

        $env           = Setting::get('mpesa_env', env('MPESA_ENV', 'sandbox'));
        $this->baseUrl = $env === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        // Callback URL is auto-derived from APP_URL — works locally and in production
        $this->callbackUrl = rtrim(config('app.url'), '/') . '/mpesa/callback';
    }

    private function getAccessToken(): string
    {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);

        $response = Http::withHeaders(['Authorization' => 'Basic ' . $credentials])
            ->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

        if (!$response->successful()) {
            Log::error('MPesa: access token failed', ['body' => $response->body()]);
            throw new \RuntimeException('Could not connect to M-Pesa. Check your Daraja credentials.');
        }

        return $response->json('access_token');
    }

    private function normalisePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        $phone = ltrim($phone, '+');
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }
        return $phone;
    }

    public function stkPush(User $user, SubscriptionPlan $plan, string $phone, ?string $schoolName = null, ?\App\Models\Coupon $coupon = null): MpesaTransaction
    {
        $discount = $coupon ? $coupon->discountFor((int) $plan->price_kes) : 0;
        $amount   = max(1, $plan->price_kes - $discount); // M-Pesa minimum is KES 1; 100%-off is handled before this

        $token     = $this->getAccessToken();
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $phone     = $this->normalisePhone($phone);

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $this->shortcode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => $amount,
                'PartyA'            => $phone,
                'PartyB'            => $this->shortcode,
                'PhoneNumber'       => $phone,
                'CallBackURL'       => $this->callbackUrl,
                'AccountReference'  => $this->accountRef,
                'TransactionDesc'   => $plan->name . ' – PesaQuest',
            ]);

        if (!$response->successful() || $response->json('ResponseCode') !== '0') {
            $msg = $response->json('errorMessage') ?? $response->json('ResultDesc') ?? 'STK Push failed.';
            Log::error('MPesa STK Push failed', ['response' => $response->json()]);
            throw new \RuntimeException($msg);
        }

        return MpesaTransaction::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'checkout_request_id'  => $response->json('CheckoutRequestID'),
            'merchant_request_id'  => $response->json('MerchantRequestID'),
            'phone'                => $phone,
            'school_name'          => $schoolName,
            'amount'               => $amount,
            'coupon_id'            => $coupon?->id,
            'discount_kes'         => $discount,
            'status'               => 'pending',
        ]);
    }

    /**
     * Activate a plan without an M-Pesa payment — used for 100%-off coupons.
     * Creates a completed zero-amount transaction so history stays consistent.
     */
    public function activateFree(User $user, SubscriptionPlan $plan, \App\Models\Coupon $coupon, ?string $schoolName = null): MpesaTransaction
    {
        $txn = MpesaTransaction::create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'checkout_request_id'  => 'COUPON-' . strtoupper(Str::random(16)),
            'phone'                => $user->phone ?? '',
            'school_name'          => $schoolName,
            'amount'               => 0,
            'coupon_id'            => $coupon->id,
            'discount_kes'         => $plan->price_kes,
            'status'               => 'completed',
            'completed_at'         => now(),
        ]);

        $this->activateSubscription($txn, 'COUPON-' . $coupon->code);

        return $txn;
    }

    public function handleCallback(array $body): void
    {
        $stk = $body['Body']['stkCallback'] ?? null;
        if (!$stk) {
            Log::warning('MPesa callback: unexpected body structure', $body);
            return;
        }

        $checkoutId = $stk['CheckoutRequestID'];
        $resultCode = (int) $stk['ResultCode'];
        $resultDesc = $stk['ResultDesc'] ?? '';

        $txn = MpesaTransaction::where('checkout_request_id', $checkoutId)->first();
        if (!$txn) {
            Log::warning('MPesa callback: unknown checkout ID', ['id' => $checkoutId]);
            return;
        }

        if ($resultCode === 0) {
            // Success — extract receipt number
            $items   = collect($stk['CallbackMetadata']['Item'] ?? []);
            $receipt = $items->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;

            $txn->update([
                'status'        => 'completed',
                'mpesa_receipt' => $receipt,
                'callback_data' => $stk,
                'completed_at'  => now(),
            ]);

            $this->activateSubscription($txn, $receipt);
        } else {
            $txn->update([
                'status'         => 'failed',
                'failure_reason' => $resultDesc,
                'callback_data'  => $stk,
            ]);
        }
    }

    private function activateSubscription(MpesaTransaction $txn, ?string $receipt): void
    {
        $plan = $txn->plan;
        $user = $txn->user;

        // Renewing while a plan is still running STACKS instead of overwriting —
        // the new plan is scheduled to start the moment the current one ends, so
        // no paid-for time is ever lost. Only a genuinely expired/finished
        // subscription's slot is reused starting from right now.
        $existingActive = $user->activeSubscription();
        $startsAt = $existingActive ? $existingActive->ends_at->copy() : now();
        $endsAt   = $startsAt->copy()->addMonths($plan->months);

        $coupon = $txn->coupon_id ? \App\Models\Coupon::find($txn->coupon_id) : null;

        Subscription::create([
            'user_id'                    => $user->id,
            'plan'                       => $plan->key,
            'plan_id'                    => $plan->id,
            'status'                     => 'active',
            'starts_at'                  => $startsAt,
            'ends_at'                    => $endsAt,
            'payment_method'             => $txn->amount > 0 ? 'mpesa' : 'coupon',
            'amount_paid'                => $txn->amount,
            'coupon_code'                => $coupon?->code,
            'discount_kes'               => $txn->discount_kes ?? 0,
            'payment_reference'          => $receipt,
            'mpesa_checkout_request_id'  => $txn->checkout_request_id,
            'mpesa_receipt'              => $receipt,
        ]);

        // Count the redemption only once payment actually succeeded
        $coupon?->increment('redemptions_count');

        $portalUrl = null;

        // For school plans — auto-create a school subscription record
        if ($plan->isSchool()) {
            $schoolName = $txn->school_name ?? ($user->name . '\'s School');
            $school = SchoolSubscription::create([
                'school_name'  => $schoolName,
                'contact_email'=> $user->email,
                'seats'        => $plan->seats ?? 30,
                'starts_at'    => now(),
                'ends_at'      => $endsAt,
                'status'       => 'active',
                'portal_token' => Str::random(48),
                'price_kes'    => $plan->price_kes,
                'created_by'   => $user->id,
            ]);
            $portalUrl = rtrim(config('app.url'), '/') . '/school/' . $school->portal_token;
        }

        // In-game notification — stacked renewals get honest "starts later" copy
        $isStacked = $existingActive !== null;
        $body = $plan->isSchool()
            ? "Your {$plan->name} school subscription is active! Share your portal to add students: {$portalUrl}"
            : ($isStacked
                ? "Payment received for {$plan->name}! Your current plan is still active until {$startsAt->format('d M Y')} — this new plan will then run until {$endsAt->format('d M Y')}. Nothing changes today; you just won't have a gap in coverage."
                : "Your {$plan->name} subscription is now active. Receipt: {$receipt}. Enjoy full PesaQuest access for {$plan->durationLabel()}!");

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'success',
            'icon'    => $plan->isSchool() ? '🏫' : ($isStacked ? '📅' : '🎉'),
            'title'   => $plan->isSchool() ? 'School Subscription Activated!' : ($isStacked ? 'Renewal Scheduled!' : 'Subscription Activated!'),
            'body'    => $body,
        ]);

        // Email notification
        try {
            $expires = $endsAt->format('d M Y');
            $emailBody = $plan->isSchool()
                ? "Hello {$user->name},\n\nYour PesaQuest school subscription ({$plan->name}) is now ACTIVE! 🏫\n\nM-Pesa Receipt: {$receipt}\nExpires: {$expires}\nSeats: {$plan->seats} students\n\nYour school portal (share with your school admin to add students):\n{$portalUrl}\n\nThank you,\nMoski Team"
                : ($isStacked
                    ? "Hello {$user->name},\n\nWe've received your payment for {$plan->name}! 🎮\n\nYour CURRENT plan remains active until {$startsAt->format('d M Y')}. Your new plan will then take over automatically and run until {$expires} — no gap in your access, and nothing changes today.\n\nM-Pesa Receipt: {$receipt}\n\nThank you,\nMoski Team"
                    : "Hello {$user->name},\n\nYour PesaQuest {$plan->name} subscription is now ACTIVE! 🎮\n\nM-Pesa Receipt: {$receipt}\nExpires: {$expires}\n\nPlay now: " . config('app.url') . "/game\n\nThank you,\nMoski Team");

            $subject = $plan->isSchool()
                ? "PesaQuest School Subscription Activated! 🏫"
                : ($isStacked
                    ? "PesaQuest {$plan->name} Renewal Scheduled! 📅"
                    : "PesaQuest {$plan->name} Subscription Activated! 🎮");

            Mail::raw($emailBody, fn($m) => $m->to($user->email)->subject($subject));
        } catch (\Throwable $e) {
            Log::warning('MPesa: email notification failed', ['error' => $e->getMessage()]);
        }
    }
}
