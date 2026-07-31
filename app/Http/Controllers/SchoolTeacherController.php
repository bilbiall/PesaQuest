<?php

namespace App\Http\Controllers;

use App\Models\GameNotification;
use App\Models\PlayerBill;
use App\Models\PlayerLifeEvent;
use App\Models\SchoolMember;
use App\Models\SchoolSubscription;
use App\Models\SchoolTeacher;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The authenticated, multi-teacher school portal — replaces "knowledge of a
 * secret URL" with real accounts. Several teachers can hold their own login
 * and see the same roster; the owner (the account that bought the plan) can
 * invite/remove co-teachers.
 */
class SchoolTeacherController extends Controller
{
    // ── Invite acceptance (public — the invitee may not have an account yet) ──

    public function showInvite(string $token)
    {
        $invite = SchoolTeacher::where('invite_token', $token)->with('school')->firstOrFail();

        if (!auth()->check()) {
            return view('school.teacher.invite-login', compact('invite'));
        }

        return view('school.teacher.invite-accept', compact('invite'));
    }

    public function acceptInvite(string $token)
    {
        $invite = SchoolTeacher::where('invite_token', $token)->with('school')->firstOrFail();

        if (!auth()->check()) {
            return redirect()->route('school.teacher.invite', $token)
                ->with('error', 'Log in or create an account first, then open this invite link again.');
        }

        if ($invite->status === 'active' && $invite->user_id !== auth()->id()) {
            abort(403, 'This invite has already been accepted by someone else.');
        }

        $invite->update([
            'user_id'     => auth()->id(),
            'status'      => 'active',
            'accepted_at' => now(),
            'name'        => $invite->name ?: auth()->user()->name,
        ]);

        return redirect()->route('school.teacher.dashboard', $invite->school_subscription_id)
            ->with('success', "You're in! Welcome to the {$invite->school->school_name} teacher portal.");
    }

    // ── Dashboard & roster (auth + school.teacher middleware) ──────────────

    public function dashboard(SchoolSubscription $school)
    {
        $members = SchoolMember::where('school_subscription_id', $school->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        $userIds = $members->pluck('user_id');

        $progressByUser = UserProgress::whereIn('user_id', $userIds)->get()->keyBy('user_id');
        $overdueByUser  = PlayerBill::whereIn('user_id', $userIds)->where('status', 'overdue')
            ->selectRaw('user_id, count(*) as c')->groupBy('user_id')->pluck('c', 'user_id');

        $roster = $members->map(function ($m) use ($progressByUser, $overdueByUser) {
            $p = $progressByUser->get($m->user_id);
            return [
                'member_id'    => $m->id,
                'user'         => $m->user,
                'level'        => $p->level ?? 1,
                'credit_score' => $p->credit_score ?? 500,
                'net_worth'    => $p->net_worth_cache ?? 0,
                'chapter'      => $p?->chapterName() ?? 'The Student',
                'overdue_bills'=> (int) ($overdueByUser[$m->user_id] ?? 0),
                'last_active'  => $p?->last_tick_at,
            ];
        })->sortByDesc('overdue_bills')->values();

        $teachers = $school->teachers()->with('user')->orderByDesc('role')->get();
        $myRole   = request()->attributes->get('schoolTeacherRole', 'teacher');

        $stats = [
            'students'      => $members->count(),
            'overdue_total' => $roster->sum('overdue_bills'),
            'avg_credit'    => $roster->isEmpty() ? 0 : (int) round($roster->avg('credit_score')),
        ];

        return view('school.teacher.dashboard', compact('school', 'roster', 'teachers', 'myRole', 'stats'));
    }

    public function student(SchoolSubscription $school, SchoolMember $member)
    {
        abort_unless($member->school_subscription_id === $school->id, 404);

        $user     = $member->user;
        $progress = UserProgress::where('user_id', $user->id)->first();

        $bills = PlayerBill::where('user_id', $user->id)->with('bill')->orderByDesc('status')->get();

        $timeline = PlayerLifeEvent::where('user_id', $user->id)
            ->with('lifeEvent')
            ->orderByDesc('tick_triggered')
            ->take(15)
            ->get();

        return view('school.teacher.student', compact('school', 'member', 'user', 'progress', 'bills', 'timeline'));
    }

    // ── Teacher management (owner only) ─────────────────────────────────────

    public function inviteTeacher(Request $request, SchoolSubscription $school): JsonResponse
    {
        $this->requireOwner($school);

        $data = $request->validate(['email' => 'required|email|max:255']);

        if (SchoolTeacher::where('school_subscription_id', $school->id)->where('email', $data['email'])->exists()) {
            return response()->json(['error' => 'This email is already a teacher (or invited) at this school.'], 422);
        }

        $existingUser = User::where('email', $data['email'])->first();
        $invite = SchoolTeacher::create([
            'school_subscription_id' => $school->id,
            'user_id'                => $existingUser?->id,
            'email'                  => $data['email'],
            'role'                   => 'teacher',
            'invite_token'           => Str::random(48),
            'status'                 => 'invited',
            'invited_by'             => auth()->id(),
        ]);

        if ($existingUser) {
            GameNotification::create([
                'user_id' => $existingUser->id,
                'type'    => 'teacher_invite',
                'title'   => "🏫 You've been invited to teach at {$school->school_name}",
                'body'    => 'Open your invite to access the teacher portal and see student progress.',
                'icon'    => '🏫',
                'data'    => ['url' => route('school.teacher.invite', $invite->invite_token)],
            ]);
        }

        return response()->json([
            'success'    => true,
            'invite_url' => route('school.teacher.invite', $invite->invite_token),
        ]);
    }

    public function removeTeacher(SchoolSubscription $school, SchoolTeacher $teacher): JsonResponse
    {
        $this->requireOwner($school);
        abort_unless($teacher->school_subscription_id === $school->id, 404);

        if ($teacher->role === 'owner') {
            return response()->json(['error' => 'The school owner cannot be removed. Ask an admin to transfer ownership.'], 422);
        }

        $teacher->delete();
        return response()->json(['success' => true]);
    }

    private function requireOwner(SchoolSubscription $school): void
    {
        if (auth()->user()->is_admin) return;
        $role = request()->attributes->get('schoolTeacherRole');
        abort_unless($role === 'owner', 403, 'Only the school owner can do this.');
    }

    // ── Student roster management (any active teacher) ─────────────────────

    public function addStudent(Request $request, SchoolSubscription $school): JsonResponse
    {
        if (!$school->isActive()) {
            return response()->json(['error' => 'This school subscription has expired or is inactive.'], 422);
        }
        if ($school->availableSeats() <= 0) {
            return response()->json(['error' => "All {$school->seats} seats are filled."], 422);
        }

        $data = $request->validate(['email' => 'required|email']);
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return response()->json(['error' => 'No PesaQuest account found with that email. The student must register first.'], 422);
        }
        if (SchoolMember::where('school_subscription_id', $school->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'This student is already a member of this school.'], 422);
        }

        $teacherName = request()->attributes->get('schoolTeacher')?->name ?? auth()->user()->name;

        SchoolMember::create([
            'school_subscription_id' => $school->id,
            'user_id'                => $user->id,
            'status'                 => 'active',
            'added_by_name'          => $teacherName,
        ]);

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'success',
            'icon'    => '🏫',
            'title'   => 'School Subscription Activated!',
            'body'    => "You've been added to {$school->school_name}'s PesaQuest subscription. You now have full access until " . $school->ends_at->format('d M Y') . '!',
        ]);

        return response()->json(['success' => true]);
    }

    public function removeStudent(SchoolSubscription $school, SchoolMember $member): JsonResponse
    {
        abort_unless($member->school_subscription_id === $school->id, 404);
        $member->delete();
        return response()->json(['success' => true]);
    }
}
