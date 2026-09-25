<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;

/**
 * ТЗ 7.3.2 — the SLA deadline passed.
 *
 * support:flag-overdue sends this exactly once per ticket, ever.
 */
final class TicketOverdue extends SupportNotification
{
    public function template(): SupportNotificationTemplate
    {
        return SupportNotificationTemplate::TicketOverdue;
    }

    protected function subject(): string
    {
        return "Напоминание: заявка {$this->ticket->number} просрочена — нужна срочная реакция";
    }

    protected function data(): array
    {
        return [
            'authorName'  => $this->ticket->user?->full_name,
            'authorEmail' => $this->ticket->contact_email,
            'createdAt'   => $this->ticket->created_at,
            'assignee'    => $this->ticket->assignee?->full_name,
        ];
    }
}
