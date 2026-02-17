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
     * @param  string  ...$permissions  Required permissions (e.g. 'create', 'manage', 'evaluate')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        $organization = $request->route('organization');

        if (! $organization instanceof Organization) {
            $organization = Organization::findOrFail($organization);
        }

        // Admins bypass org permission checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if (! $user->canInOrg($permission, $organization)) {
                abort(403, "Unauthorized. Missing organization permission: {$permission}.");
            }
        }

        return $next($request);
    }
}
