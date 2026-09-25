<?php

declare(strict_types=1);

namespace App\Notifications\Support;

use App\Enums\SupportNotificationTemplate;
use App\Models\SupportTicket;
use App\Services\SlaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Shared shell for every support email: sender, subject, view wiring.
 * Subclasses supply only the template, the subject line and extra view data.
 *
 * ShouldQueueAfterCommit matters. SupportTicketService::create() and addComment()
 * run inside DB::transaction(), and a queued notification serialises the ticket as
 * an id that the worker re-queries. Without it a worker can pick the job up before
 * the commit, fail to find the ticket and drop the email without a trace.
 */
abstract class SupportNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public const SENDER_USER     = 'Служба поддержки Всероссийского центра талантов «Признание»';
    public const SENDER_OPERATOR = 'Система поддержки Всероссийского центра талантов «Признание»';

    /** The scheduled queue:work runs with --tries=1, so support mail asks for its own retries. */
    public int $tries = 3;

    /**
     * Not readonly on purpose. SerializesModels restores this with reflection from
     * the subclass's scope, and PHP before 8.4 refuses to initialize a readonly
     * property declared in a parent class from a child scope — every queued letter
     * dies with «Cannot initialize readonly property … from scope …». The server
     * runs 8.3, so this must stay a plain property.
     */
    public function __construct(public SupportTicket $ticket)
    {
    }

    abstract public function template(): SupportNotificationTemplate;

    abstract protected function subject(): string;

    /**
     * Extra view data on top of common().
     *
     * @return array<string, mixed>
     */
    protected function data(): array
    {
        return [];
    }

    /**
     * Support mail always goes out: the «отключить уведомления» checkbox covers
     * contest mail only (clarified with the client on 18.09).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $template = $this->template();

        // The From address is the mailbox the server authenticates as (MAIL_FROM_ADDRESS),
        // never «Email поддержки» — that setting is the address shown to users on the
        // login page, and a From the SMTP account cannot send as is rejected or spam-binned.
        //
        // Never ->attach(): the ТЗ (7.1) allows the file count and a link, nothing more.
        return (new MailMessage)
            ->from(
                (string) config('mail.from.address'),
                $template->forOperators() ? self::SENDER_OPERATOR : self::SENDER_USER,
            )
            ->subject($this->subject())
            ->view($template->view(), array_merge(
                $this->common($notifiable),
                $this->data(),
            ));
    }

    /**
     * @return array<string, mixed>
     */
    protected function common(object $notifiable): array
    {
        return [
            'ticket'        => $this->ticket,
            'number'        => $this->ticket->number,
            'ticketSubject' => $this->ticket->subject,
            'status'        => $this->ticket->status,
            'category'      => $this->ticket->category?->name,
            'subcategory'   => $this->ticket->subcategory?->name,
            'recipientName' => $notifiable->first_name ?? null,
            // Resolved here rather than injected: this object gets serialised onto the queue.
            'slaHours'      => app(SlaService::class)->hours(),
            'deadline'      => $this->ticket->sla_deadline,
            'userUrl'       => route('tickets.show', $this->ticket),
            'operatorUrl'   => route('admin.support.tickets.show', $this->ticket),
        ];
    }

    /** How many files hang off the ticket itself — a count, never the files. */
    protected function attachmentCount(): int
    {
        return $this->ticket->attachments()->count();
    }
}
