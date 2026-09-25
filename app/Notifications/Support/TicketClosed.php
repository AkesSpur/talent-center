<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;

/**
 * ТЗ 7.2.4 — moved to «Закрыта».
 *
 * One template for all three ways a ticket closes (operator, user confirmation,
 * 3-day auto-close); closed_reason carries the difference.
 */
final class TicketClosed extends SupportNotification
{
    public function template(): SupportNotificationTemplate
    {
        return SupportNotificationTemplate::TicketClosed;
    }

    protected function subject(): string
    {
        return "Заявка {$this->ticket->number} закрыта";
    }

    protected function data(): array
    {
        return [
            'reason'   => $this->ticket->closed_reason,
            'closedAt' => $this->ticket->closed_at,
            'canRate'  => $this->ticket->csat_score === null,
        ];
    }
}
