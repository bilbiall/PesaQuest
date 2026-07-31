<?php

namespace App\Http\Controllers;

use App\Models\SchoolMember;
use App\Models\SchoolSubscription;
use App\Models\User;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function portal(string $token)
    {
        $school = SchoolSubscription::where('portal_token', $token)
            ->with(['members' => fn($q) => $q->with('user')->orderBy('created_at', 'desc')])
            ->firstOrFail();

        $challenges = \App\Models\ForumTopic::where('school_subscription_id', $school->id)
            ->where('is_challenge', true)
            ->latest()
            ->take(10)
            ->get();

        return view('school.portal', compact('school', 'challenges'));
    }

    /**
     * Teacher posts a challenge from the portal (token = authorization).
     * It appears as a pinned 🎯 challenge on the school's private forum board.
     */
    public function postChallenge(Request $request, string $token)
    {
        $school = SchoolSubscription::where('portal_token', $token)->firstOrFail();

        if (!$school->isActive()) {
            return response()->json(['error' => 'This school subscription has expired or is inactive.'], 422);
        }

        $data = $request->validate([
            'teacher_name' => 'required|string|min:2|max:80',
            'title'        => 'required|string|min:5|max:150',
            'body'         => 'required|string|min:10|max:5000',
        ]);

        // Attribute to the account that bought the school plan (fallback: any admin)
        $author = User::find($school->created_by) ?? User::where('is_admin', true)->first();
        if (!$author) {
            return response()->json(['error' => 'No account available to attribute the challenge to.'], 422);
        }

        $topic = \App\Models\ForumTopic::create([
            'user_id'                => $author->id,
            'school_subscription_id' => $school->id,
            'title'                  => $data['title'],
            'slug'                   => \Illuminate\Support\Str::slug(\Illuminate\Support\Str::limit($data['title'], 100, '')) . '-' . strtolower(\Illuminate\Support\Str::random(4)),
            'body'                   => $data['body'],
            'category'               => 'school',
            'is_challenge'           => true,
            'is_pinned'              => true,
            'posted_by_name'         => $data['teacher_name'],
            'last_activity_at'       => now(),
        ]);

        // Ping every active student in the school
        $memberIds = SchoolMember::where('school_subscription_id', $school->id)
            ->where('status', 'active')
            ->pluck('user_id');

        foreach ($memberIds as $uid) {
            \App\Models\GameNotification::create([
                'user_id' => $uid,
                'type'    => 'school_challenge',
                'title'   => '🎯 New challenge from ' . $data['teacher_name'],
                'body'    => '"' . \Illuminate\Support\Str::limit($data['title'], 70) . '" — reply on your school board to earn XP!',
                'icon'    => '🎯',
                'data'    => ['topic_id' => $topic->id, 'slug' => $topic->slug],
            ]);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Challenge posted! ' . $memberIds->count() . ' student(s) notified.',
            'slug'     => $topic->slug,
        ]);
    }

    public function addMember(Request $request, string $token)
    {
        $school = SchoolSubscription::where('portal_token', $token)->firstOrFail();

        if (!$school->isActive()) {
            return response()->json(['error' => 'This school subscription has expired or is inactive.'], 422);
        }

        if ($school->availableSeats() <= 0) {
            return response()->json(['error' => "All {$school->seats} seats are filled. Contact admin to increase the seat count."], 422);
        }

        $data = $request->validate([
            'email'      => 'required|email',
            'added_by'   => 'nullable|string|max:100',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return response()->json(['error' => 'No PesaQuest account found with that email. The student must register first.'], 422);
        }

        if (SchoolMember::where('school_subscription_id', $school->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'This student is already a member of this school.'], 422);
        }

        $member = SchoolMember::create([
            'school_subscription_id' => $school->id,
            'user_id'                => $user->id,
            'status'                 => 'active',
            'added_by_name'          => $data['added_by'] ?? 'School Portal',
        ]);

        // Notify the student
        \App\Models\GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'success',
            'icon'    => '🏫',
            'title'   => 'School Subscription Activated!',
            'body'    => "You've been added to {$school->school_name}'s PesaQuest subscription. You now have full access until " . $school->ends_at->format('d M Y') . '!',
        ]);

        return response()->json([
            'success' => true,
            'member'  => [
                'id'         => $member->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'added_at'   => $member->created_at->format('d M Y'),
                'avatar_url' => $user->profile_photo,
            ],
        ]);
    }

    public function removeMember(Request $request, string $token, SchoolMember $member)
    {
        $school = SchoolSubscription::where('portal_token', $token)->firstOrFail();

        if ($member->school_subscription_id !== $school->id) {
            abort(403, 'Member does not belong to this school.');
        }

        $userName = $member->user?->name ?? 'Student';
        $member->delete();

        return response()->json(['success' => true, 'removed_name' => $userName]);
    }
}
