<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ContestStatus;
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
     * Non-draft, non-cancelled contests are publicly visible.
     * Draft and cancelled: only org reps (can_create or can_manage) or admin.
     */
    public function view(?User $user, Contest $contest): bool
    {
        $restricted = [ContestStatus::Draft, ContestStatus::Cancelled];

        if (! in_array($contest->status, $restricted, true)) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->canInOrg('create', $contest->organization)
            || $user->canInOrg('manage', $contest->organization);
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
     * Editing is only allowed while the contest is in pending or accepting status.
     * Admin or org rep with 'create' permission.
     */
    public function update(User $user, Contest $contest): bool
    {
        if (! $contest->status->canEdit()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->canInOrg('create', $contest->organization);
    }

    /**
     * Cancelling is allowed while the contest is active (pending/accepting/evaluation).
     * Admin or org rep with 'create' OR 'manage' permission.
     */
    public function cancel(User $user, Contest $contest): bool
    {
        if (! $contest->status->canCancel()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->canInOrg('create', $contest->organization)
            || $user->canInOrg('manage', $contest->organization);
    }

    /**
     * Only admin can delete contests.
     */
    public function delete(User $user, Contest $contest): bool
    {
        return $user->isAdmin();
    }
}
