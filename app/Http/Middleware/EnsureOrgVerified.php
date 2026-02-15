<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrgVerified
{
    /**
     * Ensure the organization in the route is verified.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organization = $request->route('organization');

        if (! $organization instanceof Organization) {
            $organization = Organization::findOrFail($organization);
        }

        if (! $organization->isVerified()) {
            abort(403, 'This organization has not been verified yet.');
        }

        return $next($request);
    }
}
