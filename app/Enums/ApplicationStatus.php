<?php

declare(strict_types=1);

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'new';
    case Participant = 'participant';
    case Place1 = 'place_1';
    case Place2 = 'place_2';
    case Place3 = 'place_3';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Participant => 'Participant',
            self::Place1 => '1st Place',
            self::Place2 => '2nd Place',
            self::Place3 => '3rd Place',
            self::Rejected => 'Rejected',
        };
    }

    public function isEvaluated(): bool
    {
        return $this !== self::New;
    }
}
