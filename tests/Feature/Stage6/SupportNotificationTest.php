<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Models\SiteSettings;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\Support\NewReply;
use App\Notifications\Support\NewTicketForOperator;
use App\Notifications\Support\SupportNotification;
use App\Notifications\Support\TicketClosed;
use App\Notifications\Support\TicketCreated;
use App\Notifications\Support\TicketOverdue;
use App\Notifications\Support\TicketResolved;
use App\Notifications\Support\TicketStatusChanged;
use App\Notifications\Support\UserActivityForOperator;
use App\Services\SupportTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Who gets an email, and when. Stage 1 sent nothing at all — the client's only
 * finding after testing it was that a reply reached nobody.
 */
class SupportNotificationTest extends TestCase
{
    use RefreshDatabase;

    private SupportTicketService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SupportTicketService::class);
    }

    private function ticketFor(?User $owner, ?string $guestEmail = null): SupportTicket
    {
        $category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);

        return $this->service->create([
            'user_id'     => $owner?->id,
            'guest_email' => $guestEmail,
            'category_id' => $category->id,
            'subject'     => 'Не приходит диплом',
            'description' => 'Оплатил, диплома нет.',
        ], [], $owner);
    }

    public function test_creating_a_ticket_notifies_the_author_and_every_operator(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $admin = User::factory()->create(['role' => 'admin']);
        $support = User::factory()->create(['role' => 'support']);
        $bystander = User::factory()->create(['role' => 'participant']);

        Notification::fake();
        $this->ticketFor($owner);

        Notification::assertSentTo($owner, TicketCreated::class);
        Notification::assertSentTo($admin, NewTicketForOperator::class);
        Notification::assertSentTo($support, NewTicketForOperator::class);
        Notification::assertNotSentTo($bystander, NewTicketForOperator::class);
        Notification::assertNotSentTo($owner, NewTicketForOperator::class);
    }

    public function test_blocked_operators_are_not_notified(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $blocked = User::factory()->create(['role' => 'support', 'is_blocked' => true]);

        Notification::fake();
        $this->ticketFor($owner);

        Notification::assertNotSentTo($blocked, NewTicketForOperator::class);
    }

    public function test_a_guest_ticket_mails_the_guest_address(): void
    {
        Notification::fake();
        $this->ticketFor(null, 'guest@example.com');

        Notification::assertSentOnDemand(
            TicketCreated::class,
            fn ($notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'guest@example.com',
        );
    }

    public function test_a_reply_that_moves_the_status_sends_the_status_letter(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($owner);

        Notification::fake();
        // The first reply also moves «Новая» → «В работе».
        $this->service->addComment($ticket, $operator, 'Смотрим оплату.', [], fromOperator: true);

        Notification::assertSentToTimes($owner, TicketStatusChanged::class, 1);
        Notification::assertNotSentTo($owner, NewReply::class);
        Notification::assertNotSentTo($operator, UserActivityForOperator::class);
    }

    public function test_a_reply_without_a_status_change_sends_the_new_reply_letter(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($owner);
        $this->service->addComment($ticket, $operator, 'Смотрим оплату.', [], fromOperator: true);

        Notification::fake();
        // Already «В работе», so this one is news about the answer, not the status.
        $this->service->addComment($ticket->fresh(), $operator, 'Диплом отправлен повторно.', [], fromOperator: true);

        Notification::assertSentToTimes($owner, NewReply::class, 1);
        Notification::assertNotSentTo($owner, TicketStatusChanged::class);
    }

    public function test_an_internal_note_notifies_nobody(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($owner);

        Notification::fake();
        $this->service->addComment($ticket, $operator, 'Проверить в банке.', [], fromOperator: true, isInternal: true);

        Notification::assertNothingSent();
    }

    public function test_a_user_reply_on_an_unassigned_ticket_notifies_every_operator(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $admin = User::factory()->create(['role' => 'admin']);
        $support = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($owner);

        Notification::fake();
        $this->service->addComment($ticket, $owner, 'Всё ещё нет диплома.');

        Notification::assertSentTo($admin, UserActivityForOperator::class);
        Notification::assertSentTo($support, UserActivityForOperator::class);
        Notification::assertNothingSentTo($owner);
    }

    public function test_a_user_reply_goes_only_to_the_assigned_operator(): void
    {
        // The client's decision (24.09): once someone owns the ticket, the rest
        // of the helpdesk stops hearing about it.
        $owner = User::factory()->create(['role' => 'participant']);
        $assignee = User::factory()->create(['role' => 'support']);
        $bystander = User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticketFor($owner);
        $this->service->assign($ticket, $assignee);

        Notification::fake();
        $this->service->addComment($ticket->fresh(), $owner, 'Всё ещё нет диплома.');

        Notification::assertSentTo($assignee, UserActivityForOperator::class);
        Notification::assertNotSentTo($bystander, UserActivityForOperator::class);
    }

    public function test_a_blocked_assignee_hands_the_letter_back_to_everyone(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $assignee = User::factory()->create(['role' => 'support']);
        $other = User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticketFor($owner);
        $this->service->assign($ticket, $assignee);
        $assignee->update(['is_blocked' => true]);

        Notification::fake();
        $this->service->addComment($ticket->fresh(), $owner, 'Всё ещё нет диплома.');

        Notification::assertSentTo($other, UserActivityForOperator::class);
        Notification::assertNotSentTo($assignee, UserActivityForOperator::class);
    }

    public function test_a_new_ticket_still_reaches_the_whole_helpdesk(): void
    {
        // «Новая заявка» stays wide even though replies no longer do.
        $owner = User::factory()->create(['role' => 'participant']);
        $admin = User::factory()->create(['role' => 'admin']);
        $support = User::factory()->create(['role' => 'support']);

        Notification::fake();
        $this->ticketFor($owner);

        Notification::assertSentTo($admin, NewTicketForOperator::class);
        Notification::assertSentTo($support, NewTicketForOperator::class);
    }

    public function test_resolving_sends_only_the_resolved_letter(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($owner);
        $this->service->changeStatus($ticket, SupportTicketStatus::InProgress, $operator);

        Notification::fake();
        $this->service->changeStatus($ticket->fresh(), SupportTicketStatus::Resolved, $operator);

        Notification::assertSentTo($owner, TicketResolved::class);
        Notification::assertNotSentTo($owner, TicketStatusChanged::class);
    }

    public function test_closing_sends_only_the_closed_letter(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($owner);
        $this->service->changeStatus($ticket, SupportTicketStatus::InProgress, $operator);
        $this->service->changeStatus($ticket->fresh(), SupportTicketStatus::Resolved, $operator);

        Notification::fake();
        $this->service->changeStatus($ticket->fresh(), SupportTicketStatus::Closed, $operator);

        Notification::assertSentTo($owner, TicketClosed::class);
        Notification::assertNotSentTo($owner, TicketStatusChanged::class);
    }

    public function test_needs_clarification_sends_the_generic_status_letter(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($owner);
        $this->service->changeStatus($ticket, SupportTicketStatus::InProgress, $operator);

        Notification::fake();
        $this->service->changeStatus($ticket->fresh(), SupportTicketStatus::NeedsClarification, $operator);

        Notification::assertSentTo(
            $owner,
            TicketStatusChanged::class,
            fn (TicketStatusChanged $n) => $n->previous === SupportTicketStatus::InProgress,
        );
    }

    public function test_user_confirmation_closes_the_ticket_and_sends_the_closed_letter(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($owner);
        $this->service->changeStatus($ticket, SupportTicketStatus::InProgress, $operator);
        $this->service->changeStatus($ticket->fresh(), SupportTicketStatus::Resolved, $operator);

        Notification::fake();
        $this->service->confirmResolution($ticket->fresh());

        Notification::assertSentTo($owner, TicketClosed::class);
    }

    public function test_support_mail_ignores_the_email_notifications_flag(): void
    {
        // That checkbox covers contest mail only (clarified with the client 18.09).
        $owner = User::factory()->create(['role' => 'participant', 'email_notifications' => false]);

        Notification::fake();
        $this->ticketFor($owner);

        Notification::assertSentTo($owner, TicketCreated::class);
    }

    public function test_no_support_letter_carries_an_attachment(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticketFor($owner);

        foreach ($this->everyTemplate($ticket) as $notification) {
            $mail = $notification->toMail($owner);

            $this->assertSame([], $mail->attachments, $notification::class . ' attached a file');
            $this->assertSame([], $mail->rawAttachments, $notification::class . ' attached raw data');
        }
    }

    public function test_letters_are_signed_by_the_support_service_or_the_support_system(): void
    {
        config(['mail.from.address' => 'noreply@demo.talant-centr.ru']);

        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticketFor($owner);

        foreach ($this->everyTemplate($ticket) as $notification) {
            $expected = $notification->template()->forOperators()
                ? SupportNotification::SENDER_OPERATOR
                : SupportNotification::SENDER_USER;

            $this->assertSame(
                ['noreply@demo.talant-centr.ru', $expected],
                $notification->toMail($owner)->from,
            );
        }
    }

    public function test_the_sender_is_the_authenticated_mailbox_not_the_support_contact(): void
    {
        // «Email поддержки» is the address users are told to write to. Sending from
        // it would mean a From the SMTP account cannot authenticate as, which mail
        // servers reject and Gmail treats as spoofing.
        config(['mail.from.address' => 'noreply@demo.talant-centr.ru']);
        SiteSettings::set(SiteSettings::SUPPORT_EMAIL, 'support@talant-centr.ru');

        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticketFor($owner);

        $this->assertSame(
            'noreply@demo.talant-centr.ru',
            (new TicketCreated($ticket))->toMail($owner)->from[0],
        );
    }

    public function test_the_sla_figure_comes_from_the_settings(): void
    {
        SiteSettings::set(SiteSettings::SUPPORT_SLA_HOURS, '12');

        $owner = User::factory()->create(['role' => 'participant', 'first_name' => 'Сергей']);
        $ticket = $this->ticketFor($owner);

        $body = (new TicketCreated($ticket))->toMail($owner)->render();

        $this->assertStringContainsString('в течение 12 часов', $body);
        $this->assertStringNotContainsString('48 часов', $body);
    }

    public function test_every_template_renders(): void
    {
        $owner = User::factory()->create(['role' => 'participant', 'first_name' => 'Сергей']);
        $ticket = $this->ticketFor($owner);

        foreach ($this->everyTemplate($ticket) as $notification) {
            $body = $notification->toMail($owner)->render();

            $this->assertStringContainsString($ticket->number, $body, $notification::class . ' lost the ticket number');
            $this->assertStringContainsString('Талант-центр', $body);
        }
    }

    /** @return array<int, SupportNotification> */
    private function everyTemplate(SupportTicket $ticket): array
    {
        $comment = $this->service->addComment(
            $ticket,
            User::factory()->create(['role' => 'support']),
            'Ответ поддержки.',
            [],
            fromOperator: true,
        );

        $ticket = $ticket->fresh();

        return [
            new TicketCreated($ticket),
            new NewReply($ticket, $comment->id),
            new TicketStatusChanged($ticket, SupportTicketStatus::New),
            new TicketResolved($ticket),
            new TicketClosed($ticket),
            new NewTicketForOperator($ticket),
            new TicketOverdue($ticket),
            new UserActivityForOperator($ticket, $comment->id),
        ];
    }
}
