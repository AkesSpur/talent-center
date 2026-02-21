<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'На проверке',
            self::Verified => 'Верифицирована',
        };
    }
}
