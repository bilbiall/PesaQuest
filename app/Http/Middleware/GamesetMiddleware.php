<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GamesetMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user || (!$user->is_gameset && !$user->is_admin)) {
            abort(403, 'GameSet access required.');
        }
        return $next($request);
    }
}
