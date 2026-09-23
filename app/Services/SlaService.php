<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSettings;
use Carbon\CarbonInterface;

/**
 * Single seam for the SLA rule.
 *
 * Currently a flat calendar clock, as agreed with the client (48 hours from
 * creation to resolution). Reading the duration from settings keeps it
 * changeable without a deploy; if business hours are ever required, this is
 * the only class that has to change.
 */
class SlaService
{
    public const DEFAULT_HOURS = 48;

    public function hours(): int
    {
        $hours = (int) SiteSettings::get(SiteSettings::SUPPORT_SLA_HOURS, (string) self::DEFAULT_HOURS);

        return $hours > 0 ? $hours : self::DEFAULT_HOURS;
    }

    public function deadlineFor(CarbonInterface $createdAt): CarbonInterface
    {
        return $createdAt->copy()->addHours($this->hours());
    }
}
