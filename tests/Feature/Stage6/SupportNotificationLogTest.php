<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportNotificationTemplate;
use App\Models\SupportCategory;
use App\Models\SupportNotificationLog;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\CustomResetPassword;
use App\Notifications\Support\TicketCreated;
use App\Services\SupportTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationFailed;
use Tests\TestCase;

/**
 * The «История уведомлений» tab (ТЗ 11.2).
 *
 * Deliberately no Notification::fake() here: faking replaces the channel manager,
 * so NotificationSent never fires and nothing would ever be logged. These rely on
 * the real path — phpunit.xml sets the sync queue and the array mailer.
 */
class SupportNotificationLogTest extends TestCase
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

    public function test_every_sent_letter_is_logged_exactly_once(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $admin = User::factory()->create(['role' => 'admin']);
        $support = User::factory()->create(['role' => 'support']);

        $ticket = $this->ticketFor($owner);

        // One letter to the owner, one to each operator. A different number here
        // usually means the listener got registered twice.
        $this->assertDatabaseCount('support_notification_logs', 3);

        $this->assertDatabaseHas('support_notification_logs', [
            'ticket_id'       => $ticket->id,
            'recipient_email' => $owner->email,
            'template_type'   => SupportNotificationTemplate::TicketCreated->value,
            'status'          => SupportNotificationLog::STATUS_SENT,
        ]);

        foreach ([$admin, $support] as $operator) {
            $this->assertDatabaseHas('support_notification_logs', [
                'ticket_id'       => $ticket->id,
                'recipient_email' => $operator->email,
                'template_type'   => SupportNotificationTemplate::NewTicketForOperator->value,
                'status'          => SupportNotificationLog::STATUS_SENT,
            ]);
        }
    }

    public function test_a_failed_letter_is_logged_with_its_error(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticketFor($owner);

        event(new NotificationFailed($owner, new TicketCreated($ticket), 'mail', [
            'exception' => new \RuntimeException('SMTP 550 mailbox unavailable'),
        ]));

        $log = SupportNotificationLog::where('status', SupportNotificationLog::STATUS_FAILED)->sole();

        $this->assertSame($owner->email, $log->recipient_email);
        $this->assertStringContainsString('SMTP 550', (string) $log->error);
    }

    public function test_a_guest_letter_is_logged_against_the_guest_address(): void
    {
        $this->ticketFor(null, 'guest@example.com');

        $this->assertDatabaseHas('support_notification_logs', [
            'recipient_email' => 'guest@example.com',
            'template_type'   => SupportNotificationTemplate::TicketCreated->value,
        ]);
    }

    public function test_mail_that_is_not_support_mail_is_not_logged(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $user->notify(new CustomResetPassword('token'));

        $this->assertDatabaseCount('support_notification_logs', 0);
    }

    public function test_the_history_tab_lists_the_letters(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticketFor($owner);

        $this->actingAs($admin)
            ->get(route('admin.support.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('История уведомлений')
            ->assertSee($owner->email)
            ->assertSee(SupportNotificationTemplate::TicketCreated->label())
            ->assertSee('Отправлено');
    }

    public function test_the_history_tab_is_empty_for_a_ticket_nothing_was_sent_about(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);

        // Straight to the model: no service call, so no letters.
        $ticket = SupportTicket::create([
            'user_id'     => $owner->id,
            'category_id' => $category->id,
            'subject'     => 'Тишина',
            'description' => 'Ничего не отправлялось.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.support.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Писем по этой заявке ещё не отправлялось.');
    }

    public function test_the_user_facing_card_does_not_expose_the_history(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticketFor($owner);

        $this->actingAs($owner)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('История уведомлений');
    }
}
