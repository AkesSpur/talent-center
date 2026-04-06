<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending     = 'pending';
    case Accepted    = 'accepted';
    case Refunded    = 'refunded';
    case Distributed = 'distributed';

    public function label(): string
    {
        return match ($this) {
            self::Pending     => 'Ожидает оплаты',
            self::Accepted    => 'Принят',
            self::Refunded    => 'Возврат',
            self::Distributed => 'Разнесён',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending     => 'bg-purple-100 text-purple-700',
            self::Accepted    => 'bg-green-100 text-green-700',
            self::Refunded    => 'bg-orange-100 text-orange-700',
            self::Distributed => 'bg-blue-100 text-blue-700',
        };
    }
}
