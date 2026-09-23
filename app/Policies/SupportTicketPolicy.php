<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    /** Admins and the support role are the helpdesk operators (agreed 18.09). */
    public function operate(User $user): bool
    {
        return $user->isAdmin() || $user->isSupport();
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        return $this->operate($user) || $ticket->user_id === $user->id;
    }

    /**
     * Only the ticket owner posts through the user-facing route. Operators have
     * their own reply action — going through this one would store their message
     * as author_type "user" and skip the first-response and assignment effects.
     */
    public function comment(User $user, SupportTicket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }

    public function rate(User $user, SupportTicket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }

    public function confirm(User $user, SupportTicket $ticket): bool
    {
        return $ticket->user_id === $user->id;
    }
}
