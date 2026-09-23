<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Support;

use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Concerns\RespondsToUploadForms;
use App\Http\Controllers\Controller;
use App\Http\Requests\Support\StoreSupportTicketRequest;
use App\Http\Requests\Support\StoreTicketCommentRequest;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Operator view of the helpdesk. Reachable by the admin and support roles.
 */
class SupportTicketController extends Controller
{
    use RespondsToUploadForms;

    /** Columns an operator may sort the queue by. */
    private const SORTABLE = ['id', 'created_at', 'sla_deadline', 'status'];

    public function __construct(private readonly SupportTicketService $tickets)
    {
    }

    public function index(Request $request): View
    {
        $sort = in_array($request->query('sort'), self::SORTABLE, true)
            ? $request->query('sort')
            : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $query = SupportTicket::with(['user', 'category', 'subcategory', 'assignee'])
            ->withCount('comments');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->query('category')) {
            $query->where('category_id', (int) $category);
        }

        if ($assignee = $request->query('assignee')) {
            $assignee === 'none'
                ? $query->whereNull('assigned_to')
                : $query->where('assigned_to', (int) $assignee);
        }

        if ($from = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        return view('admin.support.tickets.index', [
            'tickets'    => $query->orderBy($sort, $direction)->paginate(20)->withQueryString(),
            'categories' => SupportCategory::roots()->ordered()->get(),
            'operators'  => $this->operators(),
            'statuses'   => SupportTicketStatus::cases(),
            'sort'       => $sort,
            'direction'  => $direction,
            'counts'     => [
                'open'    => SupportTicket::open()->count(),
                // The «Открытые» tile breaks its number down by status.
                'byStatus' => SupportTicket::open()
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status'),
                'overdue' => SupportTicket::overdue()->count(),
                'mine'    => SupportTicket::where('assigned_to', $request->user()->id)->open()->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.support.tickets.create', [
            'categories' => SupportCategory::active()->roots()->ordered()->get(),
        ]);
    }

    /** Ticket logged on behalf of a user who called or emailed (TZ section 3.2). */
    public function store(StoreSupportTicketRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'user_id.required' => 'Выберите пользователя.',
            'user_id.integer'  => 'Выберите пользователя из списка.',
            'user_id.exists'   => 'Пользователь не найден — выберите его из списка ещё раз.',
        ]);

        $ticket = $this->tickets->create(
            [
                'user_id'     => (int) $data['user_id'],
                'category_id' => (int) $request->validated('category_id'),
                'subject'     => $request->validated('subject'),
                'description' => $request->validated('description'),
            ],
            $request->file('files') ?? [],
            $request->user(),
        );

        return $this->uploadFormDone($request, route('admin.support.tickets.show', $ticket), [
            'status' => 'ticket-created',
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $this->tickets->markRead($ticket, asStaff: true);

        $ticket->load([
            'user', 'category', 'subcategory', 'assignee', 'attachments',
            'comments.author', 'comments.attachments',
        ]);

        return view('admin.support.tickets.show', [
            'ticket'      => $ticket,
            'categories'  => SupportCategory::roots()->ordered()->with('children')->get(),
            'operators'   => $this->operators(),
            'transitions' => $ticket->status->allowedTransitions(),
        ]);
    }

    public function reply(StoreTicketCommentRequest $request, SupportTicket $ticket): RedirectResponse|JsonResponse
    {
        if ($ticket->status === SupportTicketStatus::Closed) {
            return $this->uploadFormRefused($request, 'Заявка закрыта — отвечать в неё нельзя.');
        }

        $comment = $this->tickets->addComment(
            $ticket,
            $request->user(),
            $request->validated('content'),
            $request->file('files') ?? [],
            fromOperator: true,
            isInternal: $request->boolean('is_internal'),
        );

        return $this->uploadFormDone(
            $request,
            route('admin.support.tickets.show', $ticket) . '#comment-' . $comment->id,
            ['status' => 'ticket-replied'],
        );
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
        ], [
            'status.required' => 'Выберите новый статус.',
            'status.string'   => 'Выберите новый статус.',
        ]);

        $next = SupportTicketStatus::tryFrom($data['status']);

        if (! $next) {
            return back()->with('error', 'Неизвестный статус.');
        }

        try {
            $this->tickets->changeStatus($ticket, $next, $request->user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.support.tickets.show', $ticket)
            ->with('status', 'ticket-status-changed');
    }

    public function updateCategory(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            // Must be a top-level category...
            'category_id'    => [
                'required', 'integer',
                Rule::exists('support_categories', 'id')->whereNull('parent_id'),
            ],
            // ...and the subcategory must belong to that exact parent.
            'subcategory_id' => [
                'nullable', 'integer',
                Rule::exists('support_categories', 'id')
                    ->where('parent_id', (int) $request->input('category_id')),
            ],
        ], [
            'category_id.required'   => 'Выберите категорию.',
            'category_id.integer'    => 'Выберите категорию из списка.',
            'category_id.exists'     => 'Выберите категорию верхнего уровня.',
            'subcategory_id.integer' => 'Выберите подкатегорию из списка.',
            'subcategory_id.exists'  => 'Подкатегория не относится к выбранной категории.',
        ]);

        $this->tickets->changeCategory(
            $ticket,
            (int) $data['category_id'],
            $data['subcategory_id'] !== null ? (int) $data['subcategory_id'] : null,
            $request->user(),
        );

        return redirect()->route('admin.support.tickets.show', $ticket)
            ->with('status', 'ticket-category-changed');
    }

    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->tickets->assign($ticket, $request->user());

        return redirect()->route('admin.support.tickets.show', $ticket)
            ->with('status', 'ticket-assigned');
    }

    /** Autocomplete for "create a ticket on behalf of a user". */
    public function searchUsers(Request $request)
    {
        $term = trim((string) $request->query('q'));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where(function ($q) use ($term) {
                $q->where('email', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'patronymic', 'email']);

        return response()->json($users->map(fn (User $u) => [
            'id'    => $u->id,
            'name'  => $u->full_name,
            'email' => $u->email,
        ]));
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function operators()
    {
        return User::whereIn('role', ['admin', 'support'])
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'patronymic']);
    }
}
