<?php

namespace App\Http\Controllers;

use App\Models\GameNotification;
use App\Models\PlayerBill;
use App\Models\PlayerLifeEvent;
use App\Models\SchoolClass;
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
                'member_id'      => $m->id,
                'user'           => $m->user,
                'school_class_id'=> $m->school_class_id,
                'level'          => $p->level ?? 1,
                'credit_score'   => $p->credit_score ?? 500,
                'net_worth'      => $p->net_worth_cache ?? 0,
                'chapter'        => $p?->chapterName() ?? 'The Student',
                'overdue_bills'  => (int) ($overdueByUser[$m->user_id] ?? 0),
                'last_active'    => $p?->last_tick_at,
            ];
        })->sortByDesc('overdue_bills')->values();

        $teachers = $school->teachers()->with(['user', 'schoolClass'])->orderByDesc('role')->get();
        $myRole   = request()->attributes->get('schoolTeacherRole', 'teacher');
        $myTeacher = request()->attributes->get('schoolTeacher');

        $stats = [
            'students'      => $members->count(),
            'overdue_total' => $roster->sum('overdue_bills'),
            'avg_credit'    => $roster->isEmpty() ? 0 : (int) round($roster->avg('credit_score')),
        ];

        $classes = $school->classes()->withCount('members')->with('teacher.user')->orderBy('name')->get();

        $challengeTemplates = \Illuminate\Support\Facades\Schema::hasTable('challenge_templates')
            ? \App\Models\ChallengeTemplate::where('is_active', true)->where('allow_broadcast', true)->orderBy('name')->get()
            : collect();
        $classChallenges = \Illuminate\Support\Facades\Schema::hasTable('challenges')
            ? \App\Models\Challenge::where('school_subscription_id', $school->id)->withCount('participants')->latest()->take(5)->get()
            : collect();

        return view('school.teacher.dashboard', compact(
            'school', 'roster', 'teachers', 'myRole', 'myTeacher', 'stats',
            'classes', 'challengeTemplates', 'classChallenges'
        ));
    }

    // ── Classes (owner only for CRUD; any teacher can view/assign students) ──

    public function storeClass(Request $request, SchoolSubscription $school): JsonResponse
    {
        $this->requireOwner($school);

        if ($school->availableClassSlots() <= 0) {
            return response()->json(['error' => "This plan allows up to {$school->max_classes} classes. Delete one or upgrade the plan to add more."], 422);
        }

        $data = $request->validate(['name' => 'required|string|max:80']);

        if (SchoolClass::where('school_subscription_id', $school->id)->where('name', $data['name'])->exists()) {
            return response()->json(['error' => 'A class with this name already exists.'], 422);
        }

        $class = SchoolClass::create([
            'school_subscription_id' => $school->id,
            'name'                   => $data['name'],
        ]);

        return response()->json(['success' => true, 'class' => $class]);
    }

    public function updateClass(Request $request, SchoolSubscription $school, SchoolClass $class): JsonResponse
    {
        $this->requireOwner($school);
        abort_unless($class->school_subscription_id === $school->id, 404);

        $data = $request->validate([
            'name'       => 'sometimes|required|string|max:80',
            'teacher_id' => 'sometimes|nullable|exists:school_teachers,id',
        ]);

        if (!empty($data['teacher_id'])) {
            $teacher = SchoolTeacher::find($data['teacher_id']);
            abort_unless($teacher && $teacher->school_subscription_id === $school->id, 422);
        }

        $class->update($data);

        return response()->json(['success' => true, 'class' => $class->fresh()]);
    }

    public function destroyClass(SchoolSubscription $school, SchoolClass $class): JsonResponse
    {
        $this->requireOwner($school);
        abort_unless($class->school_subscription_id === $school->id, 404);

        // Students/teacher on this class simply fall back to "whole school"
        // (school_class_id nulled by the FK's nullOnDelete) — nothing is deleted.
        $class->delete();

        return response()->json(['success' => true]);
    }

    public function assignStudentClass(Request $request, SchoolSubscription $school, SchoolMember $member): JsonResponse
    {
        abort_unless($member->school_subscription_id === $school->id, 404);

        $data = $request->validate(['school_class_id' => 'nullable|exists:school_classes,id']);

        if (!empty($data['school_class_id'])) {
            $class = SchoolClass::find($data['school_class_id']);
            abort_unless($class && $class->school_subscription_id === $school->id, 422);
        }

        $member->update(['school_class_id' => $data['school_class_id'] ?? null]);

        return response()->json(['success' => true]);
    }

    /**
     * Teacher evaluation dashboard — performance of the teacher's assigned class,
     * so the school owner (or platform admin) can see if a class is engaged and
     * learning. Access: owner, platform admin (both arrive as role=owner via the
     * school.teacher middleware), or the teacher viewing their own profile.
     */
    public function teacherProfile(SchoolSubscription $school, SchoolTeacher $teacher)
    {
        abort_unless($teacher->school_subscription_id === $school->id, 404);

        $myRole = request()->attributes->get('schoolTeacherRole', 'teacher');
        $isSelf = $teacher->user_id === auth()->id();
        abort_unless($myRole === 'owner' || $isSelf, 403);

        $teacher->load('schoolClass', 'user');

        $classUserIds = collect();
        $classStats   = null;

        if ($teacher->school_class_id) {
            $classUserIds = SchoolMember::where('school_class_id', $teacher->school_class_id)
                ->where('status', 'active')
                ->pluck('user_id');

            $progress = UserProgress::whereIn('user_id', $classUserIds)->get();

            $totalQuests     = \App\Models\UserQuest::whereIn('user_id', $classUserIds)->count();
            $completedQuests = \App\Models\UserQuest::whereIn('user_id', $classUserIds)->whereNotNull('completed_at')->count();

            $participations = \App\Models\ChallengeParticipant::whereIn('user_id', $classUserIds)->get();

            $classStats = [
                'roster_size'          => $classUserIds->count(),
                'avg_net_worth'        => $progress->isEmpty() ? 0 : (int) round($progress->avg('net_worth_cache')),
                'avg_credit_score'     => $progress->isEmpty() ? 500 : (int) round($progress->avg('credit_score')),
                'avg_level'            => $progress->isEmpty() ? 1 : round($progress->avg('level'), 1),
                'quest_completion_rate'=> $totalQuests > 0 ? round($completedQuests / $totalQuests * 100, 1) : 0,
                'total_quests'         => $totalQuests,
                'completed_quests'     => $completedQuests,
                'challenge_entries'    => $participations->count(),
                'challenge_wins'       => $participations->where('is_winner', true)->count(),
            ];
        }

        return view('school.teacher.teacher-profile', compact('school', 'teacher', 'classStats'));
    }

    /** Teacher-assigned "Class Challenge" — a broadcast Challenge auto-enrolling the roster (whole school, or one class). */
    public function createClassChallenge(Request $request, SchoolSubscription $school, \App\Services\ChallengeService $service)
    {
        if (!$school->isActive()) {
            return back()->with('error', 'This school subscription has expired or is inactive.');
        }

        $data = $request->validate([
            'template_id'     => 'required|exists:challenge_templates,id',
            'duration_days'   => 'nullable|integer|min:1|max:60',
            'school_class_id' => 'nullable|exists:school_classes,id',
        ]);

        $myRole    = request()->attributes->get('schoolTeacherRole', 'teacher');
        $myTeacher = request()->attributes->get('schoolTeacher');
        $classId   = $data['school_class_id'] ?? null;

        if ($myRole !== 'owner') {
            // Non-owner teachers may only target their own assigned class, never the whole school or someone else's class.
            if (!$myTeacher?->school_class_id) {
                return back()->with('error', 'Ask the school owner to assign you to a class before launching a Class Challenge.');
            }
            $classId = $myTeacher->school_class_id;
        } elseif ($classId) {
            $class = SchoolClass::find($classId);
            if (!$class || $class->school_subscription_id !== $school->id) {
                return back()->with('error', 'Invalid class selected.');
            }
        }

        $template = \App\Models\ChallengeTemplate::where('allow_broadcast', true)->findOrFail($data['template_id']);

        $challenge = $service->createBroadcast($template, [
            'title'                  => "🏫 Class Challenge — {$template->name}",
            'scope'                  => 'school',
            'school_subscription_id' => $school->id,
            'school_class_id'        => $classId,
            'creator_id'             => auth()->id(),
            'duration_days'          => $data['duration_days'] ?? null,
        ]);

        $enrolled = $service->enrollSchoolRoster($challenge);

        return back()->with('success', "Class Challenge launched — {$enrolled} student(s) enrolled automatically.");
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
