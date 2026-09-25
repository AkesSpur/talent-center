<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;

/** ТЗ 7.3.3 — the user wrote back. */
final class UserActivityForOperator extends SupportNotification
{
    public function __construct(SupportTicket $ticket, public readonly int $commentId)
    {
        parent::__construct($ticket);
    }

    public function template(): SupportNotificationTemplate
    {
        return SupportNotificationTemplate::UserActivityForOperator;
    }

    protected function subject(): string
    {
        return "Новое сообщение от пользователя по заявке {$this->ticket->number}";
    }

    protected function data(): array
    {
        $comment = SupportTicketComment::find($this->commentId);

        return [
            'authorName'      => $this->ticket->user?->full_name,
            'authorEmail'     => $this->ticket->contact_email,
            'reply'           => $comment?->content,
            'attachmentCount' => $comment?->attachments()->count() ?? 0,
            'assignee'        => $this->ticket->assignee?->full_name,
        ];
    }
}
