<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The eight support emails (ТЗ 7.2 and 7.3, plus «Новый ответ»).
 *
 * This is the single source of truth for what gets written to
 * support_notification_logs.template_type, which Blade view renders the letter,
 * and how the «История уведомлений» tab names it.
 */
enum SupportNotificationTemplate: string
{
    // To the person who opened the ticket (ТЗ 7.2)
    case TicketCreated       = 'ticket_created';
    case NewReply            = 'new_reply';
    case TicketStatusChanged = 'ticket_status_changed';
    case TicketResolved      = 'ticket_resolved';
    case TicketClosed        = 'ticket_closed';

    // To the helpdesk (ТЗ 7.3)
    case NewTicketForOperator    = 'new_ticket_for_operator';
    case TicketOverdue           = 'ticket_overdue';
    case UserActivityForOperator = 'user_activity_for_operator';

    public function label(): string
    {
        return match ($this) {
            self::TicketCreated           => 'Заявка создана',
            self::NewReply                => 'Новый ответ поддержки',
            self::TicketStatusChanged     => 'Статус изменён',
            self::TicketResolved          => 'Заявка решена',
            self::TicketClosed            => 'Заявка закрыта',
            self::NewTicketForOperator    => 'Новая заявка',
            self::TicketOverdue           => 'Напоминание о просрочке',
            self::UserActivityForOperator => 'Действие пользователя',
        };
    }

    public function view(): string
    {
        return 'emails.support.' . str_replace('_', '-', $this->value);
    }

    /**
     * Operator letters are signed «Система поддержки», the user's «Служба поддержки».
     * The ТЗ draws that distinction deliberately — do not unify them.
     */
    public function forOperators(): bool
    {
        return in_array($this, [
            self::NewTicketForOperator,
            self::TicketOverdue,
            self::UserActivityForOperator,
        ], true);
    }
}
