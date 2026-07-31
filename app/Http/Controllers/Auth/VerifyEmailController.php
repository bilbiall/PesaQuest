<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }

    /**
     * Check if the currently authenticated user has already verified their email.
     * Used by the "I've already verified" button on the verify-email screen.
     */
    public function checkVerified(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->hasVerifiedEmail()) {
            Auth::login($user);
            return response()->json(['verified' => true, 'redirect' => route('dashboard')]);
        }
        return response()->json([
            'verified' => false,
            'message'  => 'Your email is not yet verified. Please check your inbox and click the verification link.',
        ]);
    }
}
