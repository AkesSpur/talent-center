<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SupportAttachment;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\User;

class SupportAttachmentPolicy
{
    /**
     * An attachment is readable by the helpdesk operators and by the owner of
     * the ticket it belongs to — internal notes stay operator-only.
     */
    public function download(User $user, SupportAttachment $attachment): bool
    {
        if ($user->isAdmin() || $user->isSupport()) {
            return true;
        }

        $owner = $attachment->attachable;

        if ($owner instanceof SupportTicket) {
            return $owner->user_id === $user->id;
        }

        if ($owner instanceof SupportTicketComment) {
            return ! $owner->is_internal && $owner->ticket?->user_id === $user->id;
        }

        return false;
    }
}
