<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Enums\TicketCloseReason;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\Support\TicketClosed;
use App\Notifications\Support\TicketOverdue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The two scheduled commands: support:flag-overdue every ten minutes and
 * support:auto-close hourly.
 */
class SupportSlaCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<string, mixed> $attributes */
    private function ticket(array $attributes = []): SupportTicket
    {
        $category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'participant']);

        return SupportTicket::create(array_merge([
            'user_id'      => $owner->id,
            'category_id'  => $category->id,
            'subject'      => 'Не приходит диплом',
            'description'  => 'Оплатил, диплома нет.',
            'status'       => SupportTicketStatus::InProgress,
            'sla_deadline' => now()->subHour(),
        ], $attributes));
    }

    // ── support:flag-overdue ───────────────────────────

    public function test_an_overdue_ticket_is_flagged_and_reminds_the_operators_exactly_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticket();

        Notification::fake();
        $this->artisan('support:flag-overdue')->assertSuccessful();
        // Ten minutes later the scheduler runs it again — and must stay quiet.
        $this->artisan('support:flag-overdue')->assertSuccessful();

        $ticket->refresh();
        $this->assertTrue($ticket->is_overdue);
        $this->assertNotNull($ticket->overdue_notified_at);

        Notification::assertSentToTimes($admin, TicketOverdue::class, 1);
    }

    public function test_a_ticket_inside_its_sla_is_left_alone(): void
    {
        User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticket(['sla_deadline' => now()->addDay()]);

        Notification::fake();
        $this->artisan('support:flag-overdue')->assertSuccessful();

        $this->assertFalse($ticket->fresh()->is_overdue);
        Notification::assertNothingSent();
    }

    public function test_resolved_and_closed_tickets_are_never_flagged(): void
    {
        User::factory()->create(['role' => 'admin']);
        $resolved = $this->ticket(['status' => SupportTicketStatus::Resolved, 'resolved_at' => now()]);
        $closed = $this->ticket(['status' => SupportTicketStatus::Closed, 'closed_at' => now()]);

        Notification::fake();
        $this->artisan('support:flag-overdue')->assertSuccessful();

        $this->assertFalse($resolved->fresh()->is_overdue);
        $this->assertFalse($closed->fresh()->is_overdue);
        Notification::assertNothingSent();
    }

    public function test_the_overdue_reminder_does_not_reach_the_user(): void
    {
        User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticket();

        Notification::fake();
        $this->artisan('support:flag-overdue')->assertSuccessful();

        Notification::assertNotSentTo($ticket->user, TicketOverdue::class);
    }

    public function test_the_overdue_reminder_goes_only_to_the_assigned_operator(): void
    {
        // The client's decision (24.09): an owned ticket is that operator's problem.
        $assignee = User::factory()->create(['role' => 'support']);
        $bystander = User::factory()->create(['role' => 'admin']);
        $this->ticket(['assigned_to' => $assignee->id]);

        Notification::fake();
        $this->artisan('support:flag-overdue')->assertSuccessful();

        Notification::assertSentTo($assignee, TicketOverdue::class);
        Notification::assertNotSentTo($bystander, TicketOverdue::class);
    }

    public function test_an_unassigned_overdue_ticket_reminds_the_whole_helpdesk(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $support = User::factory()->create(['role' => 'support']);
        $this->ticket();

        Notification::fake();
        $this->artisan('support:flag-overdue')->assertSuccessful();

        Notification::assertSentTo($admin, TicketOverdue::class);
        Notification::assertSentTo($support, TicketOverdue::class);
    }

    // ── support:auto-close ─────────────────────────────

    public function test_a_resolved_ticket_closes_after_three_days(): void
    {
        $ticket = $this->ticket([
            'status'      => SupportTicketStatus::Resolved,
            'resolved_at' => now()->subDays(4),
        ]);

        Notification::fake();
        $this->artisan('support:auto-close')->assertSuccessful();

        $ticket->refresh();
        $this->assertSame(SupportTicketStatus::Closed, $ticket->status);
        $this->assertSame(TicketCloseReason::AutoInactivity, $ticket->closed_reason);
        $this->assertNotNull($ticket->closed_at);

        Notification::assertSentTo($ticket->user, TicketClosed::class);
    }

    public function test_a_recently_resolved_ticket_stays_open(): void
    {
        $ticket = $this->ticket([
            'status'      => SupportTicketStatus::Resolved,
            'resolved_at' => now()->subDays(2),
        ]);

        Notification::fake();
        $this->artisan('support:auto-close')->assertSuccessful();

        $this->assertSame(SupportTicketStatus::Resolved, $ticket->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_auto_close_measures_resolved_at_not_updated_at(): void
    {
        // support:flag-overdue writes updated_at every ten minutes. If the timer
        // read that column instead, a resolved ticket would never close.
        $ticket = $this->ticket([
            'status'      => SupportTicketStatus::Resolved,
            'resolved_at' => now()->subDays(4),
        ]);

        $ticket->touch();

        Notification::fake();
        $this->artisan('support:auto-close')->assertSuccessful();

        $this->assertSame(SupportTicketStatus::Closed, $ticket->fresh()->status);
    }

    public function test_open_and_already_closed_tickets_are_untouched(): void
    {
        $open = $this->ticket(['status' => SupportTicketStatus::InProgress]);
        $closed = $this->ticket([
            'status'        => SupportTicketStatus::Closed,
            'closed_at'     => now()->subDays(9),
            'closed_reason' => TicketCloseReason::UserConfirmed,
        ]);

        Notification::fake();
        $this->artisan('support:auto-close')->assertSuccessful();

        $this->assertSame(SupportTicketStatus::InProgress, $open->fresh()->status);
        $this->assertSame(TicketCloseReason::UserConfirmed, $closed->fresh()->closed_reason);
        Notification::assertNothingSent();
    }

    public function test_the_auto_close_is_written_to_the_action_log(): void
    {
        $ticket = $this->ticket([
            'status'      => SupportTicketStatus::Resolved,
            'resolved_at' => now()->subDays(4),
        ]);

        Notification::fake();
        $this->artisan('support:auto-close')->assertSuccessful();

        $this->assertDatabaseHas('action_logs', [
            'action'      => 'support.ticket.closed',
            'target_type' => SupportTicket::class,
            'target_id'   => $ticket->id,
        ]);
    }

    public function test_an_auto_closed_ticket_can_still_be_rated(): void
    {
        $ticket = $this->ticket([
            'status'      => SupportTicketStatus::Resolved,
            'resolved_at' => now()->subDays(4),
        ]);

        Notification::fake();
        $this->artisan('support:auto-close')->assertSuccessful();

        $this->actingAs($ticket->user)
            ->post(route('tickets.rate', $ticket), ['csat_score' => 5])
            ->assertRedirect();

        $this->assertSame(5, $ticket->fresh()->csat_score);
    }

    public function test_the_three_day_window_elapses_in_real_time(): void
    {
        $ticket = $this->ticket([
            'status'      => SupportTicketStatus::Resolved,
            'resolved_at' => now(),
        ]);

        Notification::fake();
        $this->artisan('support:auto-close')->assertSuccessful();
        $this->assertSame(SupportTicketStatus::Resolved, $ticket->fresh()->status);

        $this->travel(4)->days();
        $this->artisan('support:auto-close')->assertSuccessful();

        $this->assertSame(SupportTicketStatus::Closed, $ticket->fresh()->status);
    }
}
