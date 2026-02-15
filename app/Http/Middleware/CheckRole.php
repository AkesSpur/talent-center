<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        if ($user->isBlocked()) {
            auth()->logout();
            abort(403, 'Your account has been blocked.');
        }

        foreach ($roles as $role) {
            if ($user->role->value === $role) {
                return $next($request);
            }
        }

        abort(403, 'You do not have the required role to access this resource.');
    }
}
