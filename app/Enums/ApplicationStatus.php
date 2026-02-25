<?php

declare(strict_types=1);

namespace App\Enums;

enum ApplicationStatus: string
{
    case New         = 'new';
    case Participant = 'participant';
    case Place1      = 'place_1';
    case Place2      = 'place_2';
    case Place3      = 'place_3';
    case Rejected    = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New         => 'Новая',
            self::Participant => 'Участник',
            self::Place1      => '1 место',
            self::Place2      => '2 место',
            self::Place3      => '3 место',
            self::Rejected    => 'Отклонена',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New         => 'bg-gray-100 text-gray-700',
            self::Participant => 'bg-blue-100 text-blue-700',
            self::Place1      => 'bg-yellow-100 text-yellow-800',
            self::Place2      => 'bg-gray-200 text-gray-600',
            self::Place3      => 'bg-orange-100 text-orange-700',
            self::Rejected    => 'bg-red-100 text-red-700',
        };
    }

    public function isEvaluated(): bool
    {
        return $this !== self::New;
    }
}
