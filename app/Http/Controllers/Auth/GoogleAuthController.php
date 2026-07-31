<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

/**
 * Google Sign-In — enabled only once an admin has configured real credentials
 * (see AppServiceProvider::configureGoogleOAuthFromDatabase()); the login/
 * register views hide the button entirely until then.
 */
class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        abort_unless($this->isConfigured(), 404);

        return Socialite::driver('google')
            ->redirectUrl(route('auth.google.callback'))
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless($this->isConfigured(), 404);

        try {
            $googleUser = Socialite::driver('google')->redirectUrl(route('auth.google.callback'))->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Could not sign in with Google — please try again.');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            // An existing email/password account signing in with Google for the
            // first time gets linked rather than duplicated.
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId()]);
            } else {
                $user = User::create([
                    'name'              => $googleUser->getName() ?: 'Player',
                    'email'             => $googleUser->getEmail(),
                    'google_id'         => $googleUser->getId(),
                    // Never used to sign in (no password-based login path is ever
                    // offered for this value) — same pattern already used for the
                    // Robo bot account. The player can set a real password later
                    // via the normal forgot-password flow if they want one.
                    'password'          => Hash::make(Str::random(40)),
                    'email_verified_at' => now(), // Google already verified this address
                ]);
                $user->ensureUsername();
                event(new Registered($user));
            }
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();
        $request->session()->forget(['pending_quest_completions', 'pending_step_fires']);

        // date_of_birth drives age_group, which the rest of the app leans on
        // heavily (content gating, minors' push policy, etc.) — Google never
        // supplies it, so a brand-new account needs one short extra step
        // before it has everything a normal signup already has.
        if (!$user->date_of_birth) {
            return redirect()->route('auth.google.complete-profile');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function showCompleteProfile(): View|RedirectResponse
    {
        if (auth()->user()->date_of_birth) {
            return redirect()->route('dashboard');
        }

        return view('auth.google-complete-profile');
    }

    public function saveCompleteProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date_of_birth' => ['required', 'date', 'before:' . now()->subYears(5)->format('Y-m-d'), 'after:1920-01-01'],
        ]);

        $dob = \Carbon\Carbon::parse($data['date_of_birth']);

        auth()->user()->update([
            'date_of_birth' => $dob->toDateString(),
            'age_group'     => User::ageGroupFromDob($dob),
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }
}
