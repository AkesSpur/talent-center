<?php

declare(strict_types=1);

namespace App\Enums;

enum FileType: string
{
    case Image = 'image';
    case Document = 'document';
    case Link = 'link';
}
