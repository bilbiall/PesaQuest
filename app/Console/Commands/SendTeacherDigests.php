<?php

namespace App\Console\Commands;

use App\Models\GameNotification;
use App\Models\PlayerBill;
use App\Models\SchoolMember;
use App\Models\SchoolTeacher;
use App\Models\UserProgress;
use Illuminate\Console\Command;

/**
 * Weekly digest for every active teacher: how their class is doing, and a
 * gentle nudge toward a discussion moment if several students share the same
 * struggle (e.g. overdue bills — a natural "let's talk about budgeting" cue).
 */
class SendTeacherDigests extends Command
{
    protected $signature = 'teachers:weekly-digest';

    protected $description = 'Send each active school teacher a weekly summary of their students\' progress';

    public function handle(): int
    {
        $teachers = SchoolTeacher::where('status', 'active')->whereNotNull('user_id')->with('school')->get();
        $sent = 0;

        foreach ($teachers as $teacher) {
            if (!$teacher->school || !$teacher->school->isActive()) continue;

            $memberIds = SchoolMember::where('school_subscription_id', $teacher->school_subscription_id)
                ->where('status', 'active')->pluck('user_id');

            if ($memberIds->isEmpty()) continue;

            $overdueCount = PlayerBill::whereIn('user_id', $memberIds)->where('status', 'overdue')->distinct('user_id')->count('user_id');
            $avgCredit    = (int) round(UserProgress::whereIn('user_id', $memberIds)->avg('credit_score') ?? 500);

            $body = "{$memberIds->count()} student(s) active. ";
            $body .= $overdueCount > 0
                ? "{$overdueCount} have overdue bills right now — a good moment to discuss budgeting."
                : 'Nobody has an overdue bill this week — nice work!';
            $body .= " Average credit score: {$avgCredit}.";

            GameNotification::create([
                'user_id' => $teacher->user_id,
                'type'    => 'teacher_digest',
                'title'   => "🏫 Weekly class summary: {$teacher->school->school_name}",
                'body'    => $body,
                'icon'    => '📊',
                'data'    => ['url' => route('school.teacher.dashboard', $teacher->school_subscription_id)],
            ]);
            $sent++;
        }

        $this->info("Sent {$sent} teacher digest(s).");
        return self::SUCCESS;
    }
}
