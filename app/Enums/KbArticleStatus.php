<?php

declare(strict_types=1);

namespace App\Enums;

enum KbArticleStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Archived  = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Черновик',
            self::Published => 'Опубликована',
            self::Archived  => 'В архиве',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'bg-gray-100 text-gray-600',
            self::Published => 'bg-green-100 text-green-700',
            self::Archived  => 'bg-orange-100 text-orange-700',
        };
    }
}
