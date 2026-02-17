<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Anyone authenticated can view the list of organizations.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Anyone authenticated can view an organization.
     */
    public function view(User $user, Organization $organization): bool
    {
        return true;
    }

    /**
     * Admin, support, or any participant can create an organization.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSupport() || $user->isParticipant();
    }

    /**
     * Admin, support, or org representatives with 'manage' permission can update.
     */
    public function update(User $user, Organization $organization): bool
    {
        if ($user->isAdmin() || $user->isSupport()) {
            return true;
        }

        return $user->canInOrg('manage', $organization);
    }

    /**
     * Only admin can delete organizations.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin or support can verify organizations.
     */
    public function verify(User $user, Organization $organization): bool
    {
        return $user->isAdmin() || $user->isSupport();
    }
}
