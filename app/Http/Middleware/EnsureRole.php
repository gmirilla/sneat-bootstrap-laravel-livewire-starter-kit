<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict a route to the given users.role values, e.g. ->middleware('role:admin,superadmin').
 * Deny-by-default: an unauthenticated or unlisted user gets a 403.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, $roles, true), 403, 'You do not have permission to access this resource.');

        return $next($request);
    }
}
