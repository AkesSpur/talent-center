<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SupportTicket;
use App\Services\SupportNotifier;
use Illuminate\Console\Command;

class FlagOverdueTickets extends Command
{
    protected $signature = 'support:flag-overdue';

    protected $description = 'Flag tickets past their SLA deadline and remind the operators once.';

    public function __construct(private readonly SupportNotifier $notifier)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $flagged = 0;

        SupportTicket::query()
            ->overdue()                   // open() + sla_deadline < now()
            ->where('is_overdue', false)  // only newly breached tickets
            ->each(function (SupportTicket $ticket) use (&$flagged): void {
                // Claim the row before sending anything. This command runs every ten
                // minutes; without the claim a breached ticket would remind every
                // operator 144 times a day, forever.
                $claimed = SupportTicket::query()
                    ->whereKey($ticket->getKey())
                    ->where('is_overdue', false)
                    ->update(['is_overdue' => true, 'overdue_notified_at' => now()]);

                if ($claimed === 0) {
                    return;
                }

                $ticket->refresh();

                $this->notifier->overdue($ticket);
                $flagged++;
            });

        $this->info("Flagged {$flagged} overdue ticket(s).");

        return Command::SUCCESS;
    }
}
