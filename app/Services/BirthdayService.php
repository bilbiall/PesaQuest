<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Birthday gifts + automatic age-group transitions, driven by the user's
 * private date_of_birth (never displayed anywhere — used only here).
 *
 * Called opportunistically from LifeSimulator::processLogin, so it works on
 * shared hosting without a cron. Both checks are cheap field comparisons and
 * only write when something actually fires.
 */
class BirthdayService
{
    /** Result keys are consumed by the WYWA/notification layer. */
    public function check(User $user): ?array
    {
        if (!$user->date_of_birth) {
            return null;
        }

        $fired = null;

        // ── Age-group transition (any day, not just birthdays — also corrects
        //    users whose group drifted or who added their DOB later) ─────────
        $expected = User::ageGroupFromDob($user->date_of_birth);
        if ($user->age_group !== $expected) {
            $old = $user->age_group;
            $user->age_group = $expected;
            $user->save();

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'age_group_up',
                'title'   => '🎓 A new chapter of Pesa City opens!',
                'body'    => "You've grown into the {$this->groupLabel($expected)} world — new bills, jobs, quests and responsibilities are now yours.",
                'icon'    => '🎓',
                'data'    => ['from' => $old, 'to' => $expected],
            ]);
            $fired = ['age_group_moved' => $expected];
        }

        // ── Birthday gift (once per real-world year) ─────────────────────────
        // Column ships in migration 2026_07_10_100000; skip silently until it
        // exists so we never gift twice for lack of tracking.
        if (!Schema::hasColumn('users', 'last_birthday_gift_year')) {
            return $fired;
        }

        $today = now('Africa/Nairobi');
        if ($this->isBirthday($user, $today) && (int) $user->last_birthday_gift_year !== (int) $today->year) {
            $kes = (int) Setting::get('birthday_gift_kes', 2500);
            $xp  = (int) Setting::get('birthday_gift_xp', 250);

            $progress = $user->getOrCreateProgress();
            $progress->balance = ($progress->balance ?? 0) + $kes;
            if ($xp > 0) {
                $progress->addPoints($xp);
            }
            $progress->save();

            $user->last_birthday_gift_year = $today->year;
            $user->save();

            $firstName = explode(' ', trim($user->name))[0] ?? 'friend';
            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'birthday',
                'title'   => "🎂 Happy Birthday, {$firstName}!",
                'body'    => "Pesa City celebrates you today — a gift of KES " . number_format($kes) . " and {$xp} XP has been added to your account. Spend it wisely… or don't, it's your birthday! 🎉",
                'icon'    => '🎂',
                'data'    => ['kes' => $kes, 'xp' => $xp, 'year' => $today->year],
            ]);

            $fired = ($fired ?? []) + ['birthday_gift' => $kes];
        }

        return $fired;
    }

    /** Month+day match; Feb 29 birthdays celebrate on Feb 28 in non-leap years. */
    private function isBirthday(User $user, \Carbon\Carbon $today): bool
    {
        $dob = $user->date_of_birth;
        if ($dob->format('m-d') === '02-29' && !$today->isLeapYear()) {
            return $today->format('m-d') === '02-28';
        }
        return $today->format('m-d') === $dob->format('m-d');
    }

    private function groupLabel(string $group): string
    {
        return match ($group) {
            '8-12'  => 'Preteens (8–12)',
            '13-17' => 'Teens (13–17)',
            '18-25' => 'Young Adults (18–25)',
            default => 'Adults (26+)',
        };
    }
}
