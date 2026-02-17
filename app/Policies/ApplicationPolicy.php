<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Admin, support, or the applicant can view applications.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * The applicant, their parent, admin, support, or org evaluators can view.
     */
    public function view(User $user, Application $application): bool
    {
        if ($user->isAdmin() || $user->isSupport()) {
            return true;
        }

        // The applicant themselves
        if ($user->id === $application->user_id) {
            return true;
        }

        // The parent of the applicant
        if ($user->id === $application->user?->parent_id) {
            return true;
        }

        // Org evaluators
        return $user->canInOrg('evaluate', $application->contest->organization)
            || $user->canInOrg('manage', $application->contest->organization);
    }

    /**
     * Participants can submit applications.
     */
    public function create(User $user): bool
    {
        return $user->isParticipant();
    }

    /**
     * Only evaluators (or admin) can update application status.
     */
    public function update(User $user, Application $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canInOrg('evaluate', $application->contest->organization);
    }

    /**
     * Only admin can delete applications.
     */
    public function delete(User $user, Application $application): bool
    {
        return $user->isAdmin();
    }
}
