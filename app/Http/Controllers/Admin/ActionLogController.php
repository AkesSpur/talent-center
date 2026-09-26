<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActionLogController extends Controller
{
    /**
     * Slugs the ?target_type= filter accepts. An allow-list rather than a raw
     * class name, so a query string can never reach into arbitrary models.
     *
     * @var array<string, class-string>
     */
    private const TARGET_TYPES = [
        'support_ticket'   => SupportTicket::class,
        'support_category' => SupportCategory::class,
    ];

    public function index(Request $request): View
    {
        $query = ActionLog::with('user')->orderBy('created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Everything that happened to one ticket (ТЗ 11.1). Hits the existing
        // (target_type, target_id) index.
        if ($ticketId = $request->get('ticket')) {
            $query->where('target_type', SupportTicket::class)
                ->where('target_id', (int) $ticketId);
        }

        if ($type = self::TARGET_TYPES[$request->get('target_type')] ?? null) {
            $query->where('target_type', $type);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.action-logs.index', [
            'logs'           => $logs,
            'ticket'         => $ticketId ? SupportTicket::find((int) $ticketId) : null,
            'categoryNames'  => $this->categoryNames($logs),
        ]);
    }

    /**
     * «Изменена категория заявки: [Старая] → [Новая]» needs names, but the log
     * stores ids. Resolve the handful on this page in one query rather than
     * denormalising names into every future metadata blob.
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, ActionLog>  $logs
     * @return array<int, string>
     */
    private function categoryNames($logs): array
    {
        $ids = [];

        foreach ($logs as $log) {
            if ($log->action !== 'support.ticket.category_changed') {
                continue;
            }

            foreach (['old', 'new'] as $side) {
                $ids[] = $log->metadata[$side]['category_id'] ?? null;
                $ids[] = $log->metadata[$side]['subcategory_id'] ?? null;
            }
        }

        $ids = array_filter(array_unique($ids));

        if ($ids === []) {
            return [];
        }

        // Cast the keys: pluck returns int keys on SQLite and can return strings
        // on MySQL, which would silently render dashes in production only.
        return SupportCategory::whereIn('id', $ids)
            ->pluck('name', 'id')
            ->mapWithKeys(fn (string $name, $id) => [(int) $id => $name])
            ->all();
    }
}
