<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SupportTicketStatus;
use App\Enums\UserRole;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\User;
use App\Notifications\Support\NewReply;
use App\Notifications\Support\NewTicketForOperator;
use App\Notifications\Support\SupportNotification;
use App\Notifications\Support\TicketClosed;
use App\Notifications\Support\TicketCreated;
use App\Notifications\Support\TicketOverdue;
use App\Notifications\Support\TicketResolved;
use App\Notifications\Support\TicketStatusChanged;
use App\Notifications\Support\UserActivityForOperator;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * The only class that decides who receives a support email.
 *
 * SupportTicketService calls it on every state change, so no controller can
 * forget a letter, and the SLA commands call it too.
 */
class SupportNotifier
{
    /**
     * Everyone on the helpdesk. Only «Новая заявка» goes this wide: nobody has
     * taken the ticket yet, so it is addressed to whoever picks it up first.
     *
     * @return Collection<int, User>
     */
    public function operators(): Collection
    {
        return User::query()
            ->whereIn('role', [UserRole::Admin->value, UserRole::Support->value])
            // A blocked operator cannot act on the ticket, and the letter quotes its contents.
            ->where('is_blocked', false)
            ->orderBy('id')
            ->get();
    }

    /**
     * Whoever owes this ticket an answer: the assigned operator, or the whole
     * helpdesk while it is unassigned (client's decision, 24.09).
     *
     * A blocked assignee falls back to everyone — otherwise the letter would go
     * to an account that cannot act on it, and nobody else would hear about it.
     *
     * @return Collection<int, User>
     */
    public function responsible(SupportTicket $ticket): Collection
    {
        $assignee = $ticket->assignee;

        if ($assignee && ! $assignee->is_blocked) {
            return collect([$assignee]);
        }

        return $this->operators();
    }

    public function created(SupportTicket $ticket): void
    {
        $this->toOwner($ticket, new TicketCreated($ticket));

        // Nobody owns a brand-new ticket yet, so the whole helpdesk hears about it.
        Notification::send($this->operators(), new NewTicketForOperator($ticket));
    }

    /**
     * An operator answered publicly. A first reply also moves «Новая» → «В работе»,
     * and then the status letter is the one that carries the news.
     */
    public function operatorReplied(
        SupportTicket $ticket,
        SupportTicketStatus $previous,
        SupportTicketComment $comment,
    ): void {
        $this->toOwner($ticket, $ticket->status === $previous
            ? new NewReply($ticket, $comment->id)
            : new TicketStatusChanged($ticket, $previous, $comment->content));
    }

    public function userReplied(SupportTicket $ticket, SupportTicketComment $comment): void
    {
        Notification::send($this->responsible($ticket), new UserActivityForOperator($ticket, $comment->id));
    }

    /** One event, one letter: «Решена» and «Закрыта» have their own templates. */
    public function statusChanged(SupportTicket $ticket, SupportTicketStatus $previous): void
    {
        $this->toOwner($ticket, match ($ticket->status) {
            SupportTicketStatus::Resolved => new TicketResolved($ticket),
            SupportTicketStatus::Closed   => new TicketClosed($ticket),
            default                       => new TicketStatusChanged($ticket, $previous),
        });
    }

    public function closed(SupportTicket $ticket): void
    {
        $this->toOwner($ticket, new TicketClosed($ticket));
    }

    public function overdue(SupportTicket $ticket): void
    {
        Notification::send($this->responsible($ticket), new TicketOverdue($ticket));
    }

    /** The ticket's author, or an on-demand route for a ticket opened by a guest. */
    private function owner(SupportTicket $ticket): User|AnonymousNotifiable|null
    {
        if ($ticket->user) {
            return $ticket->user;
        }

        return $ticket->guest_email
            ? Notification::route('mail', $ticket->guest_email)
            : null;
    }

    private function toOwner(SupportTicket $ticket, SupportNotification $notification): void
    {
        $this->owner($ticket)?->notify($notification);
    }
}
