<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Participant = 'participant';
    case Support = 'support';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Администратор',
            self::Participant => 'Участник',
            self::Support => 'Поддержка',
        };
    }
}
