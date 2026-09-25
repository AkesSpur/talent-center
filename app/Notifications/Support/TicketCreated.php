<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;

/** ТЗ 7.2.1 — to the author, the moment the ticket is created. */
final class TicketCreated extends SupportNotification
{
    public function template(): SupportNotificationTemplate
    {
        return SupportNotificationTemplate::TicketCreated;
    }

    protected function subject(): string
    {
        return "Заявка {$this->ticket->number} создана — мы уже работаем над вашим вопросом";
    }

    protected function data(): array
    {
        return [
            'attachmentCount' => $this->attachmentCount(),
            'createdAt'       => $this->ticket->created_at,
        ];
    }
}
