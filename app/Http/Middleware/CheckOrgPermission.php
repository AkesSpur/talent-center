<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrgPermission
{
    /**
     * Handle an incoming request.
     *
     * Expects the route to have an {organization} parameter.
     *
     * @param  string  ...$permissions  One or more of: create, manage, evaluate
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // Admin bypasses org permission checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        $organization = $request->route('organization');

        if (! $organization instanceof Organization) {
            $organization = Organization::findOrFail($organization);
        }

        foreach ($permissions as $permission) {
            if ($user->canInOrg($permission, $organization)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have the required organization permissions.');
    }
}
