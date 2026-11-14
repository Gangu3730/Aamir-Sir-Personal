<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Usage: ->middleware('role:moderator') (moderator + admin) or 'role:admin'. */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role = 'moderator'): Response
    {
        $user = $request->user();

        $allowed = $user && $user->isApproved() && ($role === 'admin' ? $user->isAdmin() : $user->isModerator());

        abort_unless($allowed, 403);

        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        return $response;
    }
}
