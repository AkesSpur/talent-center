<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SupportTicketStatus;
use App\Enums\TicketCloseReason;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Every ticket state change goes through here, so status rules, timestamps
 * and action logging stay in one place and remain testable without HTTP.
 *
 * Email notifications are deliberately not dispatched yet — that is stage 2.
 */
class SupportTicketService
{
    public function __construct(
        private readonly SlaService $sla,
        private readonly SupportAttachmentService $attachments,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    public function create(array $data, array $files = [], ?User $createdBy = null): SupportTicket
    {
        return DB::transaction(function () use ($data, $files, $createdBy) {
            $ticket = SupportTicket::create([
                'user_id'        => $data['user_id'] ?? null,
                'guest_email'    => $data['guest_email'] ?? null,
                'category_id'    => $data['category_id'],
                'subcategory_id' => $data['subcategory_id'] ?? null,
                'subject'        => $data['subject'],
                'description'    => $data['description'],
                'status'         => SupportTicketStatus::New,
                'sla_deadline'   => $this->sla->deadlineFor(now()),
                // The opening message is the user's first one; no operator has seen it yet.
                'last_user_message_at' => now(),
            ]);

            if ($files !== []) {
                // The opening message's files hang off the ticket itself.
                $this->attachments->attachMany($ticket, $files, "tickets/{$ticket->id}");
            }

            ActionLogService::log('support.ticket.created', $ticket, [
                'subject'     => $ticket->subject,
                'category_id' => $ticket->category_id,
                'created_by'  => $createdBy?->id,
            ]);

            return $ticket;
        });
    }

    /**
     * Add a comment and apply the automatic transitions from TZ section 5.2.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    public function addComment(
        SupportTicket $ticket,
        User $author,
        string $content,
        array $files = [],
        bool $fromOperator = false,
        bool $isInternal = false,
    ): SupportTicketComment {
        return DB::transaction(function () use ($ticket, $author, $content, $files, $fromOperator, $isInternal) {
            $comment = $ticket->comments()->create([
                'author_id'   => $author->id,
                'author_type' => $fromOperator ? SupportTicketComment::AUTHOR_ADMIN : SupportTicketComment::AUTHOR_USER,
                'content'     => $content,
                'is_internal' => $fromOperator && $isInternal,
            ]);

            if ($files !== []) {
                $this->attachments->attachMany($comment, $files, "tickets/{$ticket->id}");
            }

            if ($fromOperator) {
                $this->applyOperatorReplyEffects($ticket, $author, $isInternal);
            } else {
                $this->applyUserReplyEffects($ticket);
            }

            ActionLogService::log('support.ticket.replied', $ticket, [
                'comment_id'  => $comment->id,
                'author_type' => $comment->author_type,
                'is_internal' => $comment->is_internal,
            ]);

            return $comment;
        });
    }

    /**
     * Opening a ticket clears its «new message» mark for that side. The helpdesk
     * shares one mark: once any operator opens a ticket, it is read for all of them.
     */
    public function markRead(SupportTicket $ticket, bool $asStaff): void
    {
        if (! ($asStaff ? $ticket->hasUnreadForStaff() : $ticket->hasUnreadForOwner())) {
            return;
        }

        $column = $asStaff ? 'staff_read_at' : 'user_read_at';

        // Reading is not a change to the ticket: leave updated_at and events alone.
        SupportTicket::withoutTimestamps(fn () => $ticket->forceFill([$column => now()])->saveQuietly());
    }

    /** An operator's first public reply moves "New" into "In Progress". */
    private function applyOperatorReplyEffects(SupportTicket $ticket, User $operator, bool $isInternal): void
    {
        $changes = ['last_operator_id' => $operator->id];

        if (! $isInternal) {
            // An internal note stays invisible to the user, so it is no news for them.
            $changes['last_staff_message_at'] = now();
        }

        if (! $isInternal && $ticket->first_responded_at === null) {
            $changes['first_responded_at'] = now();
        }

        if (! $isInternal && $ticket->status === SupportTicketStatus::New) {
            $changes['status'] = SupportTicketStatus::InProgress;
        }

        if ($ticket->assigned_to === null) {
            $changes['assigned_to'] = $operator->id;
        }

        $ticket->update($changes);
    }

    /** A user's reply reopens a ticket that was waiting on them or resolved. */
    private function applyUserReplyEffects(SupportTicket $ticket): void
    {
        $changes = ['last_user_message_at' => now()];

        if (in_array($ticket->status, [SupportTicketStatus::NeedsClarification, SupportTicketStatus::Resolved], true)) {
            $changes['status'] = SupportTicketStatus::InProgress;
            $changes['resolved_at'] = null;
        }

        $ticket->update($changes);
    }

    /** @throws \DomainException when the transition is not allowed */
    public function changeStatus(SupportTicket $ticket, SupportTicketStatus $next, User $operator): SupportTicket
    {
        $current = $ticket->status;

        if ($current === $next) {
            return $ticket;
        }

        if (! $current->canTransitionTo($next)) {
            throw new \DomainException(
                "Переход «{$current->label()}» → «{$next->label()}» недопустим."
            );
        }

        $changes = ['status' => $next, 'last_operator_id' => $operator->id];

        if ($next === SupportTicketStatus::Resolved) {
            $changes['resolved_at'] = now();
        }

        if ($next === SupportTicketStatus::Closed) {
            $changes['closed_at'] = now();
            $changes['closed_reason'] = TicketCloseReason::Other;
        }

        if ($next === SupportTicketStatus::InProgress) {
            $changes['resolved_at'] = null;
        }

        $ticket->update($changes);

        ActionLogService::log('support.ticket.status_changed', $ticket, [
            'old' => $current->value,
            'new' => $next->value,
        ]);

        return $ticket;
    }

    /** The user confirms the solution themselves (TZ section 5.2). */
    public function confirmResolution(SupportTicket $ticket): SupportTicket
    {
        if ($ticket->status !== SupportTicketStatus::Resolved) {
            throw new \DomainException('Подтвердить можно только решённую заявку.');
        }

        $ticket->update([
            'status'        => SupportTicketStatus::Closed,
            'closed_at'     => now(),
            'closed_reason' => TicketCloseReason::UserConfirmed,
        ]);

        ActionLogService::log('support.ticket.closed', $ticket, [
            'reason' => TicketCloseReason::UserConfirmed->value,
        ]);

        return $ticket;
    }

    public function assign(SupportTicket $ticket, User $operator): SupportTicket
    {
        $previous = $ticket->assigned_to;

        $ticket->update([
            'assigned_to'      => $operator->id,
            'last_operator_id' => $operator->id,
        ]);

        ActionLogService::log('support.ticket.assigned', $ticket, [
            'old' => $previous,
            'new' => $operator->id,
        ]);

        return $ticket;
    }

    public function changeCategory(SupportTicket $ticket, int $categoryId, ?int $subcategoryId, User $operator): SupportTicket
    {
        $old = [
            'category_id'    => $ticket->category_id,
            'subcategory_id' => $ticket->subcategory_id,
        ];

        $ticket->update([
            'category_id'      => $categoryId,
            'subcategory_id'   => $subcategoryId,
            'last_operator_id' => $operator->id,
        ]);

        ActionLogService::log('support.ticket.category_changed', $ticket, [
            'old' => $old,
            'new' => ['category_id' => $categoryId, 'subcategory_id' => $subcategoryId],
        ]);

        return $ticket;
    }

    public function rate(SupportTicket $ticket, int $score): SupportTicket
    {
        if (! $ticket->canBeRated()) {
            throw new \DomainException('Оценить можно решённую или закрытую заявку.');
        }

        $ticket->update([
            'csat_score'        => $score,
            'csat_submitted_at' => now(),
        ]);

        ActionLogService::log('support.ticket.csat_submitted', $ticket, ['score' => $score]);

        return $ticket;
    }
}
