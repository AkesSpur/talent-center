<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Console\Command;

class AutoCloseTickets extends Command
{
    protected $signature = 'support:auto-close';

    protected $description = 'Close resolved tickets after 3 days without activity.';

    private const INACTIVITY_DAYS = 3;

    public function __construct(private readonly SupportTicketService $tickets)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $closed = 0;
        $cutoff = now()->subDays(self::INACTIVITY_DAYS);

        SupportTicket::query()
            ->where('status', SupportTicketStatus::Resolved->value)
            ->whereNotNull('resolved_at')
            // resolved_at, never updated_at: support:flag-overdue writes updated_at
            // every ten minutes, so an updated_at timer would never elapse. A user's
            // reply already nulls resolved_at, so "no activity" needs no other test.
            ->where('resolved_at', '<', $cutoff)
            ->each(function (SupportTicket $ticket) use (&$closed): void {
                $this->tickets->autoClose($ticket);
                $closed++;
            });

        $this->info("Auto-closed {$closed} ticket(s).");

        return Command::SUCCESS;
    }
}
