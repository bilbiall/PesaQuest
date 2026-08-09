<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\UploadsImages;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use UploadsImages;


    /**
     * Life chapter metadata (icon/label/color) for a display badge — name and
     * icon come from the SAME admin-configurable source as everywhere else
     * (GameSet Hub → Life Chapters via UserProgress::chapters()), so this can
     * never drift if an admin renames/re-icons a chapter. Color is a pure
     * visual accent with no admin config surface, so a small static palette
     * for JUST that field is fine — it's not player-facing "content" like
     * name/icon are.
     */
    private static function chapterMeta(?string $key): array
    {
        $colors = ['student' => '#6366f1', 'graduate' => '#8b5cf6', 'hustler' => '#f59e0b', 'settler' => '#10b981', 'builder' => '#06b6d4', 'elder' => '#fbbf24'];
        $chapters = \App\Models\UserProgress::chapters();
        $row = collect($chapters)->firstWhere('key', $key) ?? $chapters[0];

        return [
            'icon'  => $row['icon'],
            'label' => $row['name'],
            'color' => $colors[$row['key']] ?? '#6366f1',
        ];
    }

    public function edit(Request $request): View
    {
        $request->user()->ensureUsername(); // backstop for accounts created before usernames existed
        $user     = $request->user()->load('progress', 'streak', 'badges', 'subscription', 'playerAssets');
        $progress = $user->progress;
        $streak   = $user->streak;
        $badges   = $user->badges;
        $sub      = $user->subscription;

        $chapterMeta = self::chapterMeta($progress?->life_chapter);
        $portfolioValue = $user->portfolio_value;

        return view('profile.edit', compact('user', 'progress', 'streak', 'badges', 'sub', 'chapterMeta', 'portfolioValue'))
            ->with('counties', User::COUNTIES);
    }

    public function show(User $user): View
    {
        $user->load('progress', 'streak', 'badges', 'playerAssets', 'subscription');

        $progress = $user->progress;

        // Last 6 completed decisions (path_history entries with lesson)
        $recentDecisions = [];
        if ($progress && $progress->path_history) {
            $history = is_array($progress->path_history) ? $progress->path_history : [];
            $recentDecisions = array_slice(array_reverse($history), 0, 6);
        }

        $chapterMeta    = self::chapterMeta($progress?->life_chapter);
        $portfolioValue = $user->portfolio_value;

        // Log Out only makes sense on YOUR OWN profile — this page is also
        // used by admins/the player-search feature to view OTHER players,
        // where a Log Out button made no sense (and logged the ADMIN out).
        $isOwnProfile = auth()->id() === $user->id;

        // Trophy Case — owned Dreams + won Challenges, same display family as Badges
        $ownedDreams = \Illuminate\Support\Facades\Schema::hasTable('dreams')
            ? \App\Models\PlayerDream::where('user_id', $user->id)->with('dream')->latest('purchased_at')->get()
            : collect();
        $wonChallenges = \Illuminate\Support\Facades\Schema::hasTable('challenges')
            ? \App\Models\ChallengeParticipant::where('user_id', $user->id)->where('is_winner', true)
                ->with('challenge.template')->latest('updated_at')->get()
            : collect();

        return view('profile.show', [
            'user'           => $user,
            'progress'       => $progress,
            'streak'         => $user->streak,
            'badges'         => $user->badges,
            'recentDecisions'=> $recentDecisions,
            'chapterMeta'    => $chapterMeta,
            'portfolioValue' => $portfolioValue,
            'isOwnProfile'   => $isOwnProfile,
            'ownedDreams'    => $ownedDreams,
            'wonChallenges'  => $wonChallenges,
        ]);
    }

    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return view('profile.search', ['users' => collect(), 'q' => $q]);
        }

        // Name/username search only — emails are private and never searchable or displayed
        $needle = ltrim($q, '@');
        $users  = User::where(function ($query) use ($q, $needle) {
                $query->where('name', 'like', "%{$q}%");
                if (User::usernamesEnabled()) {
                    $query->orWhere('username', 'like', "%{$needle}%");
                }
            })
            ->with('progress', 'badges')
            ->limit(20)
            ->get();

        return view('profile.search', compact('users', 'q'));
    }

    public function update(Request $request): RedirectResponse
    {
        // Normalize the handle before validating: strip @, lowercase, trim
        if (User::usernamesEnabled() && $request->filled('username')) {
            $request->merge(['username' => strtolower(ltrim(trim($request->input('username')), '@'))]);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => User::usernamesEnabled()
                ? ['required', 'string', 'regex:' . User::USERNAME_REGEX,
                   \Illuminate\Validation\Rule::notIn(User::RESERVED_USERNAMES),
                   'unique:users,username,' . $request->user()->id]
                : 'nullable',
            'email'         => 'required|email|max:255|unique:users,email,' . $request->user()->id,
            'bio'           => 'nullable|string|max:300',
            'county'        => 'nullable|string|max:40',
            'date_of_birth' => 'nullable|date|before:' . now()->subYears(5)->format('Y-m-d') . '|after:1920-01-01',
            'profile_photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'cover_photo'   => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:8192',
        ], [
            'username.regex'  => 'Usernames must be 3–20 characters, start with a letter, and use only lowercase letters, numbers and underscores.',
            'username.not_in' => 'That username is reserved — pick another.',
            'username.unique' => 'That username is already taken.',
        ]);

        if (!User::usernamesEnabled()) {
            unset($validated['username']);
        }

        $user = $request->user();

        // DOB is private and set-once (prevents birthday-gift farming). Setting
        // it also snaps the age group; future moves happen automatically on
        // birthdays via BirthdayService.
        if (!empty($validated['date_of_birth']) && !$user->date_of_birth) {
            $dob = \Carbon\Carbon::parse($validated['date_of_birth']);
            $validated['date_of_birth'] = $dob->toDateString();
            $validated['age_group']     = User::ageGroupFromDob($dob);
        } else {
            unset($validated['date_of_birth']);
        }

        if ($request->hasFile('profile_photo')) {
            $this->deleteStoredPhoto($user->profile_photo);
            $path = $this->resizeAndStore($request->file('profile_photo'), 'profiles/avatar', 400, 400, 82);
            $validated['profile_photo'] = '/uploads/' . $path;
        }

        if ($request->hasFile('cover_photo')) {
            $this->deleteStoredPhoto($user->cover_photo);
            $path = $this->resizeAndStore($request->file('cover_photo'), 'profiles/cover', 1200, 400, 76);
            $validated['cover_photo'] = '/uploads/' . $path;
        }

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->fill($validated)->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('updatePassword', [
            'current_password'      => ['required', 'current_password'],
            'password'              => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        $request->user()->update(['password' => bcrypt($request->password)]);

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // ── Delete a stored photo ──────────────────────────────────────────────

    private function deleteStoredPhoto(?string $stored): void
    {
        if (!$stored) return;
        // Strip scheme+host if present
        $path = preg_replace('#^https?://[^/]+#', '', $stored);
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            // New path: public/uploads/...
            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        } else {
            // Legacy path: storage/profiles/... or profiles/...
            $path = preg_replace('#^storage/#', '', $path);
            Storage::disk('public')->delete($path);
        }
    }
}
