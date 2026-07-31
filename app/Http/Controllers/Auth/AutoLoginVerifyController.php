<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoLoginVerifyController extends Controller
{
    /**
     * Handle an auto-login email verification link.
     *
     * The link is signed and does not require the user to be authenticated
     * beforehand — clicking it logs them in and marks the email as verified.
     */
    public function __invoke(Request $request)
    {
        // 1. Validate the signed URL
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        // 2. Find the user
        $user = User::find($request->id);

        if (! $user) {
            abort(403, 'User not found.');
        }

        // 3. Validate the hash
        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->hash)) {
            abort(403, 'Invalid verification hash.');
        }

        // 4. Log the user in if not already authenticated
        if (! Auth::check()) {
            Auth::login($user, true); // remember = true
        }

        // 5. Mark email as verified if not already
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // 6. Redirect to dashboard with a flash message
        return redirect()->route('dashboard')
            ->with('status', 'Email verified — welcome!');
    }
}
