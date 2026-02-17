<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin and support can view any user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSupport();
    }

    /**
     * Users can view their own profile, their children, or admin/support can view anyone.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->isAdmin() || $user->isSupport()) {
            return true;
        }

        return $user->id === $model->id || $user->id === $model->parent_id;
    }

    /**
     * Only admin can create users with any role. Participants can create child participants.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isParticipant();
    }

    /**
     * Users can update their own profile or their children. Admin/support can update anyone.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->isAdmin() || $user->isSupport()) {
            return true;
        }

        return $user->id === $model->id || $user->id === $model->parent_id;
    }

    /**
     * Only admin can delete users.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
