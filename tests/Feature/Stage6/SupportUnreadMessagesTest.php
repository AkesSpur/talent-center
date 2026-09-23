<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * «New message» marks. Each side sees which tickets carry a message it hasn't
 * opened: the user sees replies from support, the helpdesk sees replies from
 * users and tickets nobody has opened yet.
 */
class SupportUnreadMessagesTest extends TestCase
{
    use RefreshDatabase;

    private SupportTicketService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SupportTicketService::class);
    }

    private function ticketFor(User $owner): SupportTicket
    {
        $category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);

        return $this->service->create([
            'user_id'     => $owner->id,
            'category_id' => $category->id,
            'subject'     => 'Не приходит диплом',
            'description' => 'Оплатил, диплома нет.',
        ], [], $owner);
    }

    public function test_a_reply_from_support_is_a_new_message_for_the_user(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($user);

        $this->actingAs($user)->get(route('tickets.show', $ticket))->assertOk();
        $this->assertFalse($ticket->fresh()->hasUnreadForOwner());

        $this->travel(5)->seconds();
        $this->service->addComment($ticket, $operator, 'Проверяем оплату.', [], fromOperator: true);

        $this->assertTrue($ticket->fresh()->hasUnreadForOwner());
        $this->actingAs($user)->get(route('tickets.index'))->assertSee('Новое сообщение от поддержки');
    }

    public function test_opening_the_ticket_clears_the_mark_for_the_user(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($user);
        $this->service->addComment($ticket, $operator, 'Проверяем оплату.', [], fromOperator: true);

        $this->travel(5)->seconds();
        $this->actingAs($user)->get(route('tickets.show', $ticket))->assertOk();

        $this->assertFalse($ticket->fresh()->hasUnreadForOwner());
        $this->actingAs($user)->get(route('tickets.index'))->assertDontSee('Новое сообщение от поддержки');
    }

    public function test_an_internal_note_is_not_a_new_message_for_the_user(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($user);
        $this->actingAs($user)->get(route('tickets.show', $ticket));

        $this->travel(5)->seconds();
        $this->service->addComment($ticket, $operator, 'Коллеги, это дубль.', [], fromOperator: true, isInternal: true);

        $this->assertFalse($ticket->fresh()->hasUnreadForOwner());
    }

    public function test_a_ticket_nobody_opened_is_a_new_message_for_the_helpdesk(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($user);

        $this->assertTrue($ticket->hasUnreadForStaff());
        $this->actingAs($operator)->get(route('admin.support.tickets.index'))->assertSee('Новое сообщение.');

        $this->actingAs($operator)->get(route('admin.support.tickets.show', $ticket))->assertOk();
        $this->assertFalse($ticket->fresh()->hasUnreadForStaff());
    }

    public function test_a_reply_from_the_user_is_a_new_message_for_the_helpdesk(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($user);
        $this->actingAs($operator)->get(route('admin.support.tickets.show', $ticket));

        $this->travel(5)->seconds();
        $this->actingAs($user)->post(route('tickets.comment', $ticket), ['content' => 'Приложил чек.'])
            ->assertRedirect();

        $this->assertTrue($ticket->fresh()->hasUnreadForStaff());
        // The user's own reply is not news for them
        $this->assertFalse($ticket->fresh()->hasUnreadForOwner());
    }

    public function test_the_sidebar_counts_tickets_with_new_messages(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticketFor($user);
        $this->actingAs($user)->get(route('tickets.show', $ticket));

        $this->travel(5)->seconds();
        $this->service->addComment($ticket, $operator, 'Ответ.', [], fromOperator: true);

        $badge = '#title="Заявки с новыми сообщениями">\s*1\s*<#';
        $this->assertMatchesRegularExpression($badge, $this->actingAs($user)->get(route('dashboard'))->getContent());
        $this->assertMatchesRegularExpression($badge, $this->actingAs($operator)->get(route('dashboard'))->getContent());
    }

    public function test_reading_a_ticket_does_not_touch_its_own_timestamps(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticketFor($user);
        $updatedAt = $ticket->updated_at;

        $this->travel(2)->minutes();
        $this->actingAs($user)->get(route('tickets.show', $ticket))->assertOk();

        $this->assertTrue($updatedAt->equalTo($ticket->fresh()->updated_at));
    }
}
