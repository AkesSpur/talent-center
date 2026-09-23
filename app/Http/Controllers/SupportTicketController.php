<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Concerns\RespondsToUploadForms;
use App\Http\Requests\Support\StoreSupportTicketRequest;
use App\Http\Requests\Support\StoreTicketCommentRequest;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * User-facing helpdesk: the "Поддержка" section of the personal account.
 */
class SupportTicketController extends Controller
{
    use RespondsToUploadForms;

    public function __construct(private readonly SupportTicketService $tickets)
    {
    }

    public function index(Request $request): View
    {
        $showAll = $request->boolean('all');

        $query = SupportTicket::where('user_id', $request->user()->id)
            ->with(['category', 'subcategory'])
            ->latest();

        if (! $showAll) {
            $query->open();
        }

        return view('support.tickets.index', [
            'tickets'    => $query->paginate(15)->withQueryString(),
            'showAll'    => $showAll,
            'openCount'  => SupportTicket::where('user_id', $request->user()->id)->open()->count(),
            'totalCount' => SupportTicket::where('user_id', $request->user()->id)->count(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('support.tickets.create', [
            'categories' => SupportCategory::active()->roots()->ordered()->get(),
            'selected'   => (int) $request->query('category', 0),
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse|JsonResponse
    {
        $ticket = $this->tickets->create(
            [
                'user_id'     => $request->user()->id,
                'category_id' => (int) $request->validated('category_id'),
                'subject'     => $request->validated('subject'),
                'description' => $request->validated('description'),
            ],
            $request->file('files') ?? [],
            $request->user(),
        );

        return $this->uploadFormDone($request, route('tickets.show', $ticket), [
            'status'        => 'ticket-created',
            'ticket_number' => $ticket->number,
        ]);
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        // Operators may open a user's ticket from this route too, so only the owner's visit counts.
        if ($ticket->user_id === $request->user()->id) {
            $this->tickets->markRead($ticket, asStaff: false);
        }

        $ticket->load([
            'user', 'category', 'subcategory', 'attachments',
            'publicComments.author', 'publicComments.attachments',
        ]);

        return view('support.tickets.show', compact('ticket'));
    }

    public function comment(StoreTicketCommentRequest $request, SupportTicket $ticket): RedirectResponse|JsonResponse
    {
        $this->authorize('comment', $ticket);

        if ($ticket->status === SupportTicketStatus::Closed) {
            return $this->uploadFormRefused($request, 'Заявка закрыта. Создайте новую заявку.');
        }

        $comment = $this->tickets->addComment(
            $ticket,
            $request->user(),
            $request->validated('content'),
            $request->file('files') ?? [],
        );

        return $this->uploadFormDone(
            $request,
            route('tickets.show', $ticket) . '#comment-' . $comment->id,
            ['status' => 'ticket-comment-added'],
        );
    }

    public function confirm(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('confirm', $ticket);

        try {
            $this->tickets->confirmResolution($ticket);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket)->with('status', 'ticket-confirmed');
    }

    public function rate(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('rate', $ticket);

        $data = $request->validate([
            'csat_score' => ['required', 'integer', 'min:1', 'max:5'],
        ], [
            'csat_score.required' => 'Выберите оценку от 1 до 5.',
            'csat_score.integer'  => 'Выберите оценку от 1 до 5.',
            'csat_score.min'      => 'Выберите оценку от 1 до 5.',
            'csat_score.max'      => 'Выберите оценку от 1 до 5.',
        ]);

        try {
            $this->tickets->rate($ticket, (int) $data['csat_score']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tickets.show', $ticket)->with('status', 'ticket-rated');
    }
}
