<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SupportTicketStatus;
use App\Models\SupportTicket;
use App\Notifications\Support\NewReply;
use App\Notifications\Support\NewTicketForOperator;
use App\Notifications\Support\SupportNotification;
use App\Notifications\Support\TicketClosed;
use App\Notifications\Support\TicketCreated;
use App\Notifications\Support\TicketOverdue;
use App\Notifications\Support\TicketResolved;
use App\Notifications\Support\TicketStatusChanged;
use App\Notifications\Support\UserActivityForOperator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Sends one of each support letter to a real address, so deliverability and
 * wording can be checked on the server without staging a whole ticket.
 *
 *   php artisan support:test-mail you@example.com
 *   php artisan support:test-mail you@example.com --ticket=12
 *
 * It sends through the configured mailer, so run it where SMTP works — reg.ru
 * refuses submission from outside its own network.
 */
class SendSupportTestMail extends Command
{
    protected $signature = 'support:test-mail
                            {email : where to send the samples}
                            {--ticket= : an existing ticket id to use as the sample}';

    protected $description = 'Send one sample of every support email to an address.';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("«{$email}» не похоже на адрес.");

            return Command::FAILURE;
        }

        $ticket = $this->sampleTicket();

        if ($ticket === null) {
            $this->error('В базе нет ни одной заявки — создайте любую и повторите.');

            return Command::FAILURE;
        }

        $commentId = $ticket->comments()->where('is_internal', false)->value('id')
            ?? $ticket->comments()->value('id')
            ?? 0;

        $this->line("Заявка для примера: {$ticket->number}");
        $this->line('Отправитель: ' . config('mail.from.address') . ' (MAIL_FROM_ADDRESS в .env)');
        $this->newLine();

        $sent = 0;

        foreach ($this->samples($ticket, $commentId) as $notification) {
            $label = $notification->template()->label();

            try {
                Notification::route('mail', $email)->notify($notification);
                $this->info("  ✓ {$label}");
                $sent++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$label}: " . substr($e->getMessage(), 0, 160));
            }
        }

        $this->newLine();
        $this->info("Отправлено писем: {$sent} из 8. Проверьте почту, в том числе «Спам».");

        return Command::SUCCESS;
    }

    private function sampleTicket(): ?SupportTicket
    {
        $id = $this->option('ticket');

        return $id
            ? SupportTicket::find((int) $id)
            : SupportTicket::latest('id')->first();
    }

    /** @return array<int, SupportNotification> */
    private function samples(SupportTicket $ticket, int $commentId): array
    {
        return [
            new TicketCreated($ticket),
            new NewReply($ticket, $commentId),
            new TicketStatusChanged($ticket, SupportTicketStatus::New),
            new TicketResolved($ticket),
            new TicketClosed($ticket),
            new NewTicketForOperator($ticket),
            new TicketOverdue($ticket),
            new UserActivityForOperator($ticket, $commentId),
        ];
    }
}
