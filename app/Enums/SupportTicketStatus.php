<?php

declare(strict_types=1);

namespace App\Enums;

enum SupportTicketStatus: string
{
    case New                = 'new';
    case InProgress         = 'in_progress';
    case NeedsClarification = 'needs_clarification';
    case Resolved           = 'resolved';
    case Closed             = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New                => 'Новая',
            self::InProgress         => 'В работе',
            self::NeedsClarification => 'Требуется уточнение',
            self::Resolved           => 'Решена',
            self::Closed             => 'Закрыта',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New                => 'bg-blue-100 text-blue-700',
            self::InProgress         => 'bg-yellow-100 text-yellow-800',
            self::NeedsClarification => 'bg-orange-100 text-orange-700',
            self::Resolved           => 'bg-green-100 text-green-700',
            self::Closed             => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Hex for the analytics chart. Tailwind classes cannot be handed to a
     * canvas, and these are the -500 shades of the pills color() returns.
     */
    public function chartColor(): string
    {
        return match ($this) {
            self::New                => '#3B82F6',
            self::InProgress         => '#A67C00',
            self::NeedsClarification => '#F97316',
            self::Resolved           => '#22C55E',
            self::Closed             => '#9CA3AF',
        };
    }

    /** Open statuses still count against the SLA. */
    public function isOpen(): bool
    {
        return in_array($this, [self::New, self::InProgress, self::NeedsClarification], true);
    }

    /**
     * Allowed transitions — TZ section 5.2.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New                => [self::InProgress],
            self::InProgress         => [self::NeedsClarification, self::Resolved],
            self::NeedsClarification => [self::InProgress],
            self::Resolved           => [self::Closed, self::InProgress],
            self::Closed             => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
