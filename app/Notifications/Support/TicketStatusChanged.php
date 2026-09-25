<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;
use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;

/**
 * ТЗ 7.2.2 — the status moved.
 *
 * «Решена» and «Закрыта» have letters of their own, so this one covers the rest:
 * → В работе and → Требуется уточнение. One event, one email.
 */
final class TicketStatusChanged extends SupportNotification
{
    public function __construct(
        SupportTicket $ticket,
        public readonly SupportTicketStatus $previous,
        public readonly ?string $reply = null,
    ) {
        parent::__construct($ticket);
    }

    public function template(): SupportNotificationTemplate
    {
        return SupportNotificationTemplate::TicketStatusChanged;
    }

    protected function subject(): string
    {
        return "Статус заявки {$this->ticket->number} изменён — важная информация";
    }

    protected function data(): array
    {
        return [
            'previous'           => $this->previous,
            'reply'              => $this->reply,
            'needsClarification' => $this->ticket->status === SupportTicketStatus::NeedsClarification,
        ];
    }
}
