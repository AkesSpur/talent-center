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

    /**
     * The moves offered from here (ТЗ 8.4), as `target value => button label`.
     *
     * An archived article can be restored to either state on purpose:
     * archiving a category archives its drafts too, and restoring one of those
     * straight to «опубликована» would publish something nobody ever approved.
     *
     * @return array<string, string>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Draft => [
                self::Published->value => 'Опубликовать',
                self::Archived->value  => 'В архив',
            ],
            self::Published => [
                self::Draft->value    => 'Снять с публикации',
                self::Archived->value => 'В архив',
            ],
            self::Archived => [
                self::Published->value => 'Восстановить и опубликовать',
                self::Draft->value     => 'Восстановить в черновики',
            ],
        };
    }
}
