<?php

namespace App\Http\Middleware;

use App\Models\SchoolSubscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates every /school/{school}/teacher/* route: the authenticated user must
 * be an ACTIVE teacher (or owner) at that specific school. Admins may also
 * pass through, for support purposes.
 */
class EnsureSchoolTeacher
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        /** @var SchoolSubscription|null $school */
        $school = $request->route('school');

        if (!$user || !$school instanceof SchoolSubscription) {
            abort(403);
        }

        if ($user->is_admin) {
            $request->attributes->set('schoolTeacherRole', 'owner');
            return $next($request);
        }

        $teacher = $school->teacherFor($user);
        if (!$teacher) {
            abort(403, "You don't have teacher access to this school.");
        }

        $request->attributes->set('schoolTeacher', $teacher);
        $request->attributes->set('schoolTeacherRole', $teacher->role);

        return $next($request);
    }
}
