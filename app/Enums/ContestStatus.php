<?php

declare(strict_types=1);

namespace App\Enums;

enum ContestStatus: string
{
    case Draft = 'draft';
    case Accepting = 'accepting';
    case Evaluation = 'evaluation';
    case Archive = 'archive';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Accepting => 'Accepting Applications',
            self::Evaluation => 'Evaluation',
            self::Archive => 'Archive',
        };
    }
}
