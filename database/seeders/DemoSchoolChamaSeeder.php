<?php

namespace Database\Seeders;

use App\Models\Chama;
use App\Models\ChamaMember;
use App\Models\ChallengeTemplate;
use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\PlayerBill;
use App\Models\PlayerCityCourse;
use App\Models\PlayerCityJob;
use App\Models\Bill;
use App\Models\SavingsScheme;
use App\Models\SchoolClass;
use App\Models\SchoolMember;
use App\Models\SchoolSubscription;
use App\Models\SchoolTeacher;
use App\Models\User;
use App\Models\UserQuest;
use App\Models\Quest;
use App\Services\ChallengeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * NOT registered in DatabaseSeeder — this is throwaway demo data for manually
 * exercising Classes / teacher evaluation / chama battles. Run on demand:
 *   php artisan db:seed --class=DemoSchoolChamaSeeder
 * Every account uses the password "demo1234" and a @demo.test email so it's
 * obvious this is seed data, never something a real user could collide with.
 */
class DemoSchoolChamaSeeder extends Seeder
{
    private const PASSWORD = 'demo1234';

    public function run(): void
    {
        $owner     = $this->makeUser('owner.teacher@demo.test', 'Grace Owuor', 'graceowuor');
        $coteacher = $this->makeUser('coteacher@demo.test', 'James Mwangi', 'jamesmwangi');

        $students = [
            ['email' => 'student1@demo.test', 'name' => 'Amina Hassan',   'username' => 'aminahassan',   'level' => 8, 'balance' => 42000, 'credit' => 780, 'net_worth' => 95000, 'xp' => 3200],
            ['email' => 'student2@demo.test', 'name' => 'Brian Otieno',   'username' => 'brianotieno',   'level' => 6, 'balance' => 18000, 'credit' => 690, 'net_worth' => 41000, 'xp' => 2100],
            ['email' => 'student3@demo.test', 'name' => 'Cynthia Wafula', 'username' => 'cynthiawafula', 'level' => 4, 'balance' => 6000,  'credit' => 540, 'net_worth' => 9000,  'xp' => 1150],
            ['email' => 'student4@demo.test', 'name' => 'Dennis Kiptoo',  'username' => 'denniskiptoo',  'level' => 7, 'balance' => 27000, 'credit' => 710, 'net_worth' => 63000, 'xp' => 2600],
            ['email' => 'student5@demo.test', 'name' => 'Faith Njeri',    'username' => 'faithnjeri',    'level' => 5, 'balance' => 11000, 'credit' => 610, 'net_worth' => 22000, 'xp' => 1700],
            ['email' => 'student6@demo.test', 'name' => 'George Mutua',  'username' => 'georgemutua',   'level' => 3, 'balance' => 3000,  'credit' => 460, 'net_worth' => 4000,  'xp' => 800],
            ['email' => 'student7@demo.test', 'name' => 'Halima Yusuf',  'username' => 'halimayusuf',   'level' => 2, 'balance' => 1500,  'credit' => 500, 'net_worth' => 1800,  'xp' => 400],
        ];

        $users = [];
        foreach ($students as $s) {
            $u = $this->makeUser($s['email'], $s['name'], $s['username']);
            $this->giveProgress($u, $s['level'], $s['balance'], $s['credit'], $s['net_worth'], $s['xp']);
            $users[$s['email']] = $u;
        }

        // ── School subscription ──
        $school = SchoolSubscription::updateOrCreate(
            ['contact_email' => 'owner.teacher@demo.test'],
            [
                'school_name' => 'Demo Academy',
                'seats'       => 30,
                'max_classes' => 5,
                'starts_at'   => now(),
                'ends_at'     => now()->addMonths(6),
                'status'      => 'active',
                'portal_token'=> Str::random(48),
                'price_kes'   => 25000,
                'created_by'  => $owner->id,
            ]
        );

        $ownerTeacher = SchoolTeacher::updateOrCreate(
            ['school_subscription_id' => $school->id, 'email' => $owner->email],
            [
                'user_id' => $owner->id, 'name' => $owner->name, 'role' => 'owner',
                'invite_token' => Str::random(48), 'status' => 'active', 'accepted_at' => now(),
            ]
        );

        $classA = SchoolClass::updateOrCreate(['school_subscription_id' => $school->id, 'name' => 'Grade 8 Blue'], []);
        $classB = SchoolClass::updateOrCreate(['school_subscription_id' => $school->id, 'name' => 'Grade 9 Green'], []);

        $coteacherRow = SchoolTeacher::updateOrCreate(
            ['school_subscription_id' => $school->id, 'email' => $coteacher->email],
            [
                'user_id' => $coteacher->id, 'name' => $coteacher->name, 'role' => 'teacher',
                'school_class_id' => $classA->id,
                'invite_token' => Str::random(48), 'status' => 'active', 'accepted_at' => now(),
            ]
        );
        // Co-teacher is scoped to Class A only — Class B is left teacher-less so
        // you can test the owner still being able to target ANY class.

        $classAssignments = [
            'student1@demo.test' => $classA->id,
            'student2@demo.test' => $classA->id,
            'student3@demo.test' => $classA->id,
            'student4@demo.test' => $classB->id,
            'student5@demo.test' => $classB->id,
            'student6@demo.test' => $classB->id,
            'student7@demo.test' => null, // left unassigned — whole-school fallback
        ];
        foreach ($classAssignments as $email => $classId) {
            SchoolMember::updateOrCreate(
                ['school_subscription_id' => $school->id, 'user_id' => $users[$email]->id],
                ['school_class_id' => $classId, 'status' => 'active']
            );
        }

        // ── Give each student a couple of quests, a course, a job, savings, a bill ──
        $quests  = Quest::inRandomOrder()->take(10)->get();
        $courses = CityCourse::inRandomOrder()->take(5)->get();
        $jobs    = CityJob::inRandomOrder()->take(5)->get();
        $bills   = Bill::inRandomOrder()->take(5)->get();

        $i = 0;
        foreach ($users as $u) {
            if ($quests->count() >= 2) {
                UserQuest::updateOrCreate(
                    ['user_id' => $u->id, 'quest_id' => $quests[$i % $quests->count()]->id],
                    ['submitted_at' => now()->subDays(3), 'completed_at' => now()->subDays(2)]
                );
                UserQuest::updateOrCreate(
                    ['user_id' => $u->id, 'quest_id' => $quests[($i + 1) % $quests->count()]->id],
                    ['submitted_at' => null, 'completed_at' => null]
                );
            }
            if ($courses->isNotEmpty()) {
                PlayerCityCourse::updateOrCreate(
                    ['user_id' => $u->id, 'city_course_id' => $courses[$i % $courses->count()]->id],
                    ['status' => 'completed', 'enrolled_at' => now()->subDays(10), 'completed_at' => now()->subDays(8)]
                );
            }
            if ($jobs->isNotEmpty()) {
                PlayerCityJob::updateOrCreate(
                    ['user_id' => $u->id, 'city_job_id' => $jobs[$i % $jobs->count()]->id],
                    ['status' => 'employed', 'employment_type' => 'full_time', 'started_at' => now()->subDays(15), 'ticks_employed' => 15]
                );
            }
            SavingsScheme::updateOrCreate(
                ['user_id' => $u->id, 'name' => 'School Fees Fund'],
                ['target_amount' => 20000, 'current_amount' => 2000 + ($i * 1500), 'emoji' => '🏫', 'color' => '#6366f1']
            );
            if ($bills->isNotEmpty()) {
                $overdue = $i % 3 === 0; // every 3rd student has an overdue bill, for variety in the teacher dashboard
                PlayerBill::updateOrCreate(
                    ['user_id' => $u->id, 'bill_id' => $bills[$i % $bills->count()]->id],
                    [
                        'amount' => 500, 'frequency_ticks' => 30, 'next_due_tick' => $overdue ? 0 : 999,
                        'status' => $overdue ? 'overdue' : 'active',
                        'missed_count' => $overdue ? 1 : 0,
                        'overdue_since_tick' => $overdue ? 1 : null,
                    ]
                );
            }
            $i++;
        }

        // ── Two chamas so you can test "Enter My Chama" battles ──
        $chamaAlpha = Chama::firstOrCreate(
            ['name' => 'Demo Chama Alpha'],
            ['slug' => Chama::freshSlug('Demo Chama Alpha'), 'monthly_contribution' => 500, 'max_members' => 10, 'status' => 'active', 'creator_id' => $users['student1@demo.test']->id, 'pool_balance' => 15000]
        );
        ChamaMember::updateOrCreate(['chama_id' => $chamaAlpha->id, 'user_id' => $users['student1@demo.test']->id], ['role' => 'chairman', 'total_contributed' => 5000, 'share_pct' => 40, 'joined_at' => now(), 'is_active' => true]);
        ChamaMember::updateOrCreate(['chama_id' => $chamaAlpha->id, 'user_id' => $users['student2@demo.test']->id], ['role' => 'member', 'total_contributed' => 4000, 'share_pct' => 30, 'joined_at' => now(), 'is_active' => true]);
        ChamaMember::updateOrCreate(['chama_id' => $chamaAlpha->id, 'user_id' => $users['student3@demo.test']->id], ['role' => 'member', 'total_contributed' => 3000, 'share_pct' => 30, 'joined_at' => now(), 'is_active' => true]);

        $chamaBeta = Chama::firstOrCreate(
            ['name' => 'Demo Chama Beta'],
            ['slug' => Chama::freshSlug('Demo Chama Beta'), 'monthly_contribution' => 500, 'max_members' => 10, 'status' => 'active', 'creator_id' => $users['student4@demo.test']->id, 'pool_balance' => 12000]
        );
        ChamaMember::updateOrCreate(['chama_id' => $chamaBeta->id, 'user_id' => $users['student4@demo.test']->id], ['role' => 'chairman', 'total_contributed' => 4500, 'share_pct' => 40, 'joined_at' => now(), 'is_active' => true]);
        ChamaMember::updateOrCreate(['chama_id' => $chamaBeta->id, 'user_id' => $users['student5@demo.test']->id], ['role' => 'member', 'total_contributed' => 3800, 'share_pct' => 30, 'joined_at' => now(), 'is_active' => true]);
        ChamaMember::updateOrCreate(['chama_id' => $chamaBeta->id, 'user_id' => $users['student6@demo.test']->id], ['role' => 'member', 'total_contributed' => 3700, 'share_pct' => 30, 'joined_at' => now(), 'is_active' => true]);

        // ── Launch a live Class Challenge for Class A, and an open Inter-Chama Battle ──
        $service = app(ChallengeService::class);
        $broadcastTemplate = ChallengeTemplate::where('allow_broadcast', true)->where('is_active', true)->first();

        if ($broadcastTemplate) {
            $alreadyHasClassChallenge = \App\Models\Challenge::where('school_class_id', $classA->id)
                ->where('status', 'active')->exists();
            if (!$alreadyHasClassChallenge) {
                $classChallenge = $service->createBroadcast($broadcastTemplate, [
                    'title'                  => "🏫 Class Challenge — {$broadcastTemplate->name}",
                    'scope'                  => 'school',
                    'school_subscription_id' => $school->id,
                    'school_class_id'        => $classA->id,
                    'creator_id'             => $coteacher->id,
                ]);
                $service->enrollSchoolRoster($classChallenge);
            }

            $alreadyHasBattle = \App\Models\Challenge::where('is_chama_battle', true)->where('status', 'active')->exists();
            if (!$alreadyHasBattle) {
                $battle = ChallengeTemplate::where('allow_broadcast', true)->where('is_active', true)->skip(1)->first() ?? $broadcastTemplate;
                $service->createBroadcast($battle, [
                    'title'           => "⚔️ Chama Battle — {$battle->name}",
                    'is_official'     => true,
                    'is_chama_battle' => true,
                    'scope'           => 'open',
                ]);
                // Deliberately left un-entered — log in as either chairman and click
                // "Enter My Chama" on /challenges to test the flow yourself.
            }
        }

        $this->command?->info('');
        $this->command?->info('=== Demo School + Chama logins (password for all: ' . self::PASSWORD . ') ===');
        $this->command?->table(['Role', 'Name', 'Email', 'Notes'], [
            ['School Owner (teacher)', 'Grace Owuor',   'owner.teacher@demo.test', "Teacher portal: /school/{$school->id}/teacher"],
            ['Co-Teacher',             'James Mwangi',  'coteacher@demo.test',     'Scoped to Grade 8 Blue only'],
            ['Student — Class A',      'Amina Hassan',  'student1@demo.test',      'Chairman of Demo Chama Alpha'],
            ['Student — Class A',      'Brian Otieno',  'student2@demo.test',      'Member of Demo Chama Alpha'],
            ['Student — Class A',      'Cynthia Wafula','student3@demo.test',      'Member of Demo Chama Alpha, overdue bill'],
            ['Student — Class B',      'Dennis Kiptoo', 'student4@demo.test',      'Chairman of Demo Chama Beta'],
            ['Student — Class B',      'Faith Njeri',   'student5@demo.test',      'Member of Demo Chama Beta'],
            ['Student — Class B',      'George Mutua',  'student6@demo.test',      'Member of Demo Chama Beta, overdue bill'],
            ['Student — Unassigned',   'Halima Yusuf',  'student7@demo.test',      'No class — tests whole-school fallback'],
        ]);
    }

    private function makeUser(string $email, string $name, string $username): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'username'          => $username,
                'password'          => Hash::make(self::PASSWORD),
                'date_of_birth'     => '2011-01-01',
                'age_group'         => '13-17',
                'is_admin'          => false,
                'is_gameset'        => false,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
    }

    private function giveProgress(User $user, int $level, int $balance, int $credit, int $netWorth, int $xp): void
    {
        $progress = $user->getOrCreateProgress();
        $progress->level           = $level;
        $progress->balance         = $balance;
        $progress->credit_score    = $credit;
        $progress->net_worth_cache = $netWorth;
        $progress->points_total    = $xp;
        $progress->tick_count      = 40 + ($level * 5);
        $progress->last_played_at  = now()->subHours(rand(1, 48));
        $progress->last_tick_at    = now()->subHours(rand(1, 48));
        $progress->save();
    }
}
