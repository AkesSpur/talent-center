<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Support;

use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Helpdesk analytics (ТЗ 9.1).
 *
 * The series are aggregated in PHP rather than SQL on purpose: GROUP BY DATE()
 * plus TIMESTAMPDIFF would be the first MySQL-only SQL in this codebase, and the
 * test suite runs on SQLite, which has no TIMESTAMPDIFF. At one helpdesk's
 * volume over thirty days the difference is unmeasurable.
 */
class SupportDashboardController extends Controller
{
    private const DAYS = 30;

    /** Long enough to be worth caching, short enough that the trend feels live. */
    private const CACHE_SECONDS = 300;

    public const CACHE_KEY = 'support_analytics';

    public function __invoke(): View
    {
        return view('admin.support.dashboard', [
            'series'   => cache()->remember(self::CACHE_KEY, self::CACHE_SECONDS, fn () => $this->series()),
            'statuses' => $this->byStatus(),
            // Tiles stay live: «Просрочена» is computed from sla_deadline < now(),
            // so a cached number would disagree with the queue the client checks next.
            'tiles'    => [
                'inProgress' => SupportTicket::where('status', SupportTicketStatus::InProgress)->count(),
                'overdue'    => SupportTicket::overdue()->count(),
                'resolved24' => SupportTicket::where('resolved_at', '>=', now()->subDay())->count(),
            ],
        ]);
    }

    /**
     * @return array{labels: array<int, string>, created: array<int, int>, closed: array<int, int>, resolutionHours: array<int, float|null>}
     */
    private function series(): array
    {
        $from = CarbonImmutable::today()->subDays(self::DAYS - 1)->startOfDay();

        $created = $this->countByDay(
            SupportTicket::where('created_at', '>=', $from)->pluck('created_at')
        );

        $closed = $this->countByDay(
            SupportTicket::whereNotNull('closed_at')->where('closed_at', '>=', $from)->pluck('closed_at')
        );

        $resolution = $this->resolutionHoursByDay($from);

        $labels = [];
        $createdSeries = [];
        $closedSeries = [];
        $resolutionSeries = [];

        for ($i = 0; $i < self::DAYS; $i++) {
            $day = $from->addDays($i)->format('Y-m-d');

            $labels[] = $day;
            $createdSeries[] = $created[$day] ?? 0;
            $closedSeries[] = $closed[$day] ?? 0;
            // null, not 0 — a day with no resolutions is not «решено за 0 часов».
            $resolutionSeries[] = $resolution[$day] ?? null;
        }

        return [
            'labels'          => $labels,
            'created'         => $createdSeries,
            'closed'          => $closedSeries,
            'resolutionHours' => $resolutionSeries,
        ];
    }

    /**
     * @param  Collection<int, \Illuminate\Support\Carbon>  $dates
     * @return array<string, int>
     */
    private function countByDay(Collection $dates): array
    {
        return $dates
            ->groupBy(fn ($at) => $this->day($at))
            ->map(fn (Collection $group) => $group->count())
            ->all();
    }

    /** @return array<string, float> */
    private function resolutionHoursByDay(CarbonImmutable $from): array
    {
        return SupportTicket::whereNotNull('closed_at')
            ->where('closed_at', '>=', $from)
            ->get(['created_at', 'closed_at'])
            ->groupBy(fn (SupportTicket $t) => $this->day($t->closed_at))
            ->map(fn (Collection $group) => round(
                $group->avg(fn (SupportTicket $t) => $t->created_at->diffInMinutes($t->closed_at)) / 60,
                1,
            ))
            ->all();
    }

    /**
     * The app already runs on Moscow time, so this is a no-op today — but it
     * keeps the grouping correct if storage ever moves to UTC.
     */
    private function day(\DateTimeInterface $at): string
    {
        return \Illuminate\Support\Carbon::instance($at)->setTimezone('Europe/Moscow')->format('Y-m-d');
    }

    /** @return array<int, array{label: string, value: int, color: string}> */
    private function byStatus(): array
    {
        $counts = SupportTicket::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(SupportTicketStatus::cases())
            ->map(fn (SupportTicketStatus $status) => [
                'label' => $status->label(),
                'value' => (int) ($counts[$status->value] ?? 0),
                'color' => $status->chartColor(),
            ])
            ->all();
    }
}
