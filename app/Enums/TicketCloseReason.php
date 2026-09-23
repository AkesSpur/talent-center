<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketCloseReason: string
{
    case AutoInactivity = 'auto_inactivity';
    case UserConfirmed  = 'user_confirmed';
    case Other          = 'other';

    public function label(): string
    {
        return match ($this) {
            self::AutoInactivity => 'Автоматически (нет активности 3 дня)',
            self::UserConfirmed  => 'Подтверждено пользователем',
            self::Other          => 'Другое',
        };
    }

    /** How the user's ticket card finishes «Заявка закрыта …». */
    public function forUser(): string
    {
        return match ($this) {
            self::AutoInactivity => 'автоматически: после решения 3 дня не было новых сообщений',
            self::UserConfirmed  => '— вы подтвердили решение',
            self::Other          => 'сотрудником поддержки',
        };
    }
}
