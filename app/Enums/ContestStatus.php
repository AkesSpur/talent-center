<?php

declare(strict_types=1);

namespace App\Enums;

enum ContestStatus: string
{
    case Draft      = 'draft';
    case Pending    = 'pending';
    case Accepting  = 'accepting';
    case Evaluation = 'evaluation';
    case Archive    = 'archive';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft      => 'Черновик',
            self::Pending    => 'Ожидает начала приёма',
            self::Accepting  => 'Приём заявок',
            self::Evaluation => 'Оценка заявок',
            self::Archive    => 'Архив',
            self::Cancelled  => 'Отменён',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft      => 'bg-gray-100 text-gray-700',
            self::Pending    => 'bg-blue-100 text-blue-700',
            self::Accepting  => 'bg-green-100 text-green-700',
            self::Evaluation => 'bg-yellow-100 text-yellow-700',
            self::Archive    => 'bg-warm-gray/20 text-warm-gray',
            self::Cancelled  => 'bg-red-100 text-red-700',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Pending, self::Accepting, self::Evaluation], true);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Pending, self::Accepting, self::Evaluation], true);
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::Pending, self::Accepting], true);
    }

    public function canAcceptApplications(): bool
    {
        return $this === self::Accepting;
    }
}
