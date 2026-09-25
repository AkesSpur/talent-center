<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;

/**
 * Support answered and the status did not move.
 *
 * The ТЗ has no template for this, yet it is the most ordinary event on a ticket
 * and the one the client noticed was missing. ТЗ 11.3 settles it: «всё общение
 * идёт через форму в личном кабинете, почта — только для уведомлений», so the
 * user has to be told there is something to come back to.
 */
final class NewReply extends SupportNotification
{
    public function __construct(SupportTicket $ticket, public readonly int $commentId)
    {
        parent::__construct($ticket);
    }

    public function template(): SupportNotificationTemplate
    {
        return SupportNotificationTemplate::NewReply;
    }

    protected function subject(): string
    {
        return "Новый ответ по заявке {$this->ticket->number}";
    }

    protected function data(): array
    {
        $comment = SupportTicketComment::find($this->commentId);

        return [
            'reply'           => $comment?->content,
            'attachmentCount' => $comment?->attachments()->count() ?? 0,
        ];
    }
}
