<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Admin\Support\SupportDashboardController;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The helpdesk analytics page (ТЗ 9.1). */
class SupportAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private SupportCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);
    }

    /** @param array<string, mixed> $attributes */
    private function ticket(array $attributes = []): SupportTicket
    {
        // created_at is not fillable, so it has to be forced after the insert.
        $createdAt = $attributes['created_at'] ?? null;
        unset($attributes['created_at']);

        $ticket = SupportTicket::create(array_merge([
            'user_id'     => User::factory()->create(['role' => 'participant'])->id,
            'category_id' => $this->category->id,
            'subject'     => 'Не приходит диплом',
            'description' => 'Текст обращения.',
            'status'      => SupportTicketStatus::New,
        ], $attributes));

        if ($createdAt !== null) {
            SupportTicket::withoutTimestamps(
                fn () => $ticket->forceFill(['created_at' => $createdAt])->saveQuietly()
            );
        }

        return $ticket->refresh();
    }

    private function series(): array
    {
        return app(SupportDashboardController::class)->__invoke()->getData()['series'];
    }

    public function test_admins_and_operators_can_open_it_but_participants_cannot(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.support.analytics'))->assertOk();

        $this->actingAs(User::factory()->create(['role' => 'support']))
            ->get(route('admin.support.analytics'))->assertOk();

        $this->actingAs(User::factory()->create(['role' => 'participant']))
            ->get(route('admin.support.analytics'))->assertForbidden();
    }

    public function test_the_status_breakdown_matches_the_queue(): void
    {
        $this->ticket(['status' => SupportTicketStatus::New]);
        $this->ticket(['status' => SupportTicketStatus::New]);
        $this->ticket(['status' => SupportTicketStatus::Resolved, 'resolved_at' => now()]);

        $data = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.support.analytics'))->assertOk()
            ->viewData('statuses');

        $byLabel = collect($data)->keyBy('label');

        $this->assertSame(2, $byLabel[SupportTicketStatus::New->label()]['value']);
        $this->assertSame(1, $byLabel[SupportTicketStatus::Resolved->label()]['value']);
        $this->assertSame(0, $byLabel[SupportTicketStatus::Closed->label()]['value']);
    }

    public function test_quiet_days_are_zero_for_counts_and_null_for_the_average(): void
    {
        // One ticket created and closed today; every other day is empty.
        $this->ticket(['status' => SupportTicketStatus::Closed, 'closed_at' => now()]);

        $series = $this->series();

        $this->assertCount(30, $series['labels']);
        $this->assertSame(1, $series['created'][29], 'today should count one new ticket');
        $this->assertSame(0, $series['created'][0], 'an empty day counts zero, not null');

        // A day with no closures is not «решено за 0 часов».
        $this->assertNull($series['resolutionHours'][0]);
        $this->assertNotNull($series['resolutionHours'][29]);
    }

    public function test_the_average_resolution_time_is_measured_in_hours(): void
    {
        $this->ticket([
            'status'     => SupportTicketStatus::Closed,
            'created_at' => now()->subHours(5),
            'closed_at'  => now(),
        ]);
        $this->ticket([
            'status'     => SupportTicketStatus::Closed,
            'created_at' => now()->subHours(3),
            'closed_at'  => now(),
        ]);

        $series = $this->series();

        $this->assertSame(4.0, $series['resolutionHours'][29], 'the mean of 5h and 3h is 4h');
    }

    public function test_the_overdue_tile_is_live_not_cached(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.support.analytics'))
            ->assertOk()->assertViewHas('tiles', fn (array $t) => $t['overdue'] === 0);

        $this->ticket([
            'status'       => SupportTicketStatus::InProgress,
            'sla_deadline' => now()->subHour(),
        ]);

        // No cache clearing: a cached tile would disagree with the queue, which
        // is the first number the client reconciles.
        $this->actingAs($admin)->get(route('admin.support.analytics'))
            ->assertOk()->assertViewHas('tiles', fn (array $t) => $t['overdue'] === 1);
    }

    public function test_the_resolved_tile_counts_the_last_twenty_four_hours(): void
    {
        $this->ticket(['status' => SupportTicketStatus::Resolved, 'resolved_at' => now()->subHours(2)]);
        $this->ticket(['status' => SupportTicketStatus::Resolved, 'resolved_at' => now()->subDays(3)]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.support.analytics'))
            ->assertOk()
            ->assertViewHas('tiles', fn (array $t) => $t['resolved24'] === 1);
    }
}
