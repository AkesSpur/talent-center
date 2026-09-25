<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;

/** ТЗ 7.3.1 — a new ticket landed; goes to every operator (clarified 18.09). */
final class NewTicketForOperator extends SupportNotification
{
    public function template(): SupportNotificationTemplate
    {
        return SupportNotificationTemplate::NewTicketForOperator;
    }

    protected function subject(): string
    {
        return "Новая заявка {$this->ticket->number} — требуется действие";
    }

    protected function data(): array
    {
        return [
            'authorName'      => $this->ticket->user?->full_name,
            'authorEmail'     => $this->ticket->contact_email,
            'description'     => $this->ticket->description,
            'attachmentCount' => $this->attachmentCount(),
        ];
    }
}
