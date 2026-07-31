<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            // Private — drives the age group, birthday gifts and automatic
            // age-group transitions. Never displayed anywhere.
            'date_of_birth' => ['required', 'date', 'before:' . now()->subYears(5)->format('Y-m-d'), 'after:1920-01-01'],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $dob = \Carbon\Carbon::parse($request->date_of_birth);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'date_of_birth' => $dob->toDateString(),
            'age_group'     => User::ageGroupFromDob($dob),
        ]);

        // Auto-derive a unique @username from the name (editable later in Profile)
        $user->ensureUsername();

        event(new Registered($user));

        Auth::login($user);

        // Registering was never regenerating the session, so a brand-new
        // account could inherit an EARLIER session's data verbatim — including
        // queued quest-completion celebrations (session('pending_quest_completions'))
        // from whoever last used this browser tab, making it look like the new
        // account had already completed a quest it never touched.
        $request->session()->regenerate();
        $request->session()->forget(['pending_quest_completions', 'pending_step_fires']);

        return redirect(route('verification.notice'));
    }
}
