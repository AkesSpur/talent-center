<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Filtering the action log by ticket (ТЗ 11.1). */
class SupportActionLogTest extends TestCase
{
    use RefreshDatabase;

    private SupportTicketService $service;

    private SupportCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = app(SupportTicketService::class);
        $this->category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);
    }

    private function ticket(string $subject): SupportTicket
    {
        return $this->service->create([
            'user_id'     => User::factory()->create(['role' => 'participant'])->id,
            'category_id' => $this->category->id,
            'subject'     => $subject,
            'description' => 'Текст обращения.',
        ]);
    }

    public function test_the_ticket_filter_shows_only_that_tickets_history(): void
    {
        $first = $this->ticket('ПЕРВАЯ');
        $second = $this->ticket('ВТОРАЯ');

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.action-logs.index', ['ticket' => $first->id]))
            ->assertOk()
            ->assertViewHas('logs', function ($logs) use ($first) {
                return $logs->isNotEmpty()
                    && $logs->every(fn ($log) => (int) $log->target_id === $first->id
                        && $log->target_type === SupportTicket::class);
            })
            // The header names the ticket being inspected.
            ->assertSee($first->number)
            ->assertDontSee('ВТОРАЯ');

        $this->assertNotSame($first->id, $second->id);
    }

    public function test_the_object_type_filter_excludes_category_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->ticket('ЗАЯВКА');

        // A category action, which targets SupportCategory, not a ticket.
        $this->actingAs($admin)->post(route('admin.support.categories.store'), [
            'name' => 'Новая категория', 'sort_order' => 1, 'is_active' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.action-logs.index', ['target_type' => 'support_ticket']))
            ->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->isNotEmpty()
                && $logs->every(fn ($log) => $log->target_type === SupportTicket::class));
    }

    public function test_an_unknown_object_type_is_ignored_rather_than_erroring(): void
    {
        $this->ticket('ЗАЯВКА');

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.action-logs.index', ['target_type' => 'App\Models\User']))
            ->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->isNotEmpty());
    }

    public function test_a_category_change_is_rendered_with_names_not_ids(): void
    {
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticket('ЗАЯВКА');
        $target = SupportCategory::create(['name' => 'Технические вопросы', 'is_active' => true]);

        $this->service->changeCategory($ticket, $target->id, null, $operator);

        // The metadata stores ids; the page must resolve them to names.
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.action-logs.index', ['ticket' => $ticket->id]))
            ->assertOk()
            ->assertSee('Изменена категория заявки')
            ->assertSee('Оплата')
            ->assertSee('Технические вопросы');
    }

    public function test_the_history_link_is_admin_only_on_the_ticket_card(): void
    {
        $ticket = $this->ticket('ЗАЯВКА');
        $url = route('admin.action-logs.index', ['ticket' => $ticket->id]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.support.tickets.show', $ticket))
            ->assertOk()
            ->assertSee($url, false);

        // The action log lives in the admin-only group; support would get a 403.
        $this->actingAs(User::factory()->create(['role' => 'support']))
            ->get(route('admin.support.tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee($url, false);
    }

    public function test_operators_cannot_reach_the_action_log(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'support']))
            ->get(route('admin.action-logs.index'))
            ->assertForbidden();
    }
}
