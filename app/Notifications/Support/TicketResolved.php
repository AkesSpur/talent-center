<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;

/** ТЗ 7.2.3 — moved to «Решена»; asks for the 1–5 rating. */
final class TicketResolved extends SupportNotification
{
    public function template(): SupportNotificationTemplate
    {
        return SupportNotificationTemplate::TicketResolved;
    }

    protected function subject(): string
    {
        return "Заявка {$this->ticket->number} решена — оцените ответ в личном кабинете";
    }

    protected function data(): array
    {
        return [
            'reply' => $this->ticket->publicComments()->latest('id')->value('content'),
        ];
    }
}
