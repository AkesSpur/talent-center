<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contest;
use App\Models\User;

class ContestPolicy
{
    /**
     * Anyone can view the contest list.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone can view a published contest. Drafts only by org reps or admin.
     */
    public function view(?User $user, Contest $contest): bool
    {
        if ($contest->status->value !== 'draft') {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->canInOrg('create', $contest->organization);
    }

    /**
     * Org representatives with 'create' permission on a verified org can create contests.
     */
    public function create(User $user): bool
    {
        // Actual org-level permission check done via middleware
        return $user->isAdmin() || $user->isParticipant();
    }

    /**
     * Org rep with 'create' or admin can update.
     */
    public function update(User $user, Contest $contest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canInOrg('create', $contest->organization);
    }

    /**
     * Only admin can delete contests.
     */
    public function delete(User $user, Contest $contest): bool
    {
        return $user->isAdmin();
    }
}
