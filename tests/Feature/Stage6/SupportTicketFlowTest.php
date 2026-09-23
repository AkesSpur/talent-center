<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Enums\TicketCloseReason;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketFlowTest extends TestCase
{
    use RefreshDatabase;

    private function category(): SupportCategory
    {
        return SupportCategory::create([
            'name' => 'Оплата и возврат средств', 'sort_order' => 10, 'is_active' => true,
        ]);
    }

    private function user(string $role = 'participant'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_user_creates_ticket_with_sla_deadline_and_number(): void
    {
        $user = $this->user();
        $category = $this->category();

        $response = $this->actingAs($user)->post(route('tickets.store'), [
            'category_id' => $category->id,
            'subject'     => 'Не приходит чек',
            'description' => 'Оплатил вчера, чек не пришёл.',
        ]);

        $ticket = SupportTicket::first();

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame(SupportTicketStatus::New, $ticket->status);
        $this->assertSame($user->id, $ticket->user_id);
        $this->assertSame('#0001', $ticket->number);
        $this->assertNotNull($ticket->sla_deadline);
        // Default SLA is 48 hours from creation (agreed with the client).
        $this->assertEqualsWithDelta(48, $ticket->created_at->diffInHours($ticket->sla_deadline), 0.01);
    }

    public function test_ticket_attachments_go_to_the_private_disk(): void
    {
        Storage::fake('support');
        $user = $this->user();

        $this->actingAs($user)->post(route('tickets.store'), [
            'category_id' => $this->category()->id,
            'subject'     => 'Скриншот ошибки',
            'description' => 'Прилагаю скриншот.',
            'files'       => [UploadedFile::fake()->image('error.png')],
        ]);

        $ticket = SupportTicket::first();
        $attachment = $ticket->attachments()->first();

        $this->assertNotNull($attachment);
        $this->assertSame('support', $attachment->disk);
        Storage::disk('support')->assertExists($attachment->path);
    }

    public function test_user_cannot_view_another_users_ticket(): void
    {
        $owner = $this->user();
        $stranger = $this->user();

        $ticket = SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $this->category()->id,
            'subject' => 'Личное', 'description' => 'Текст', 'status' => SupportTicketStatus::New,
        ]);

        $this->actingAs($stranger)->get(route('tickets.show', $ticket))->assertForbidden();
        $this->actingAs($owner)->get(route('tickets.show', $ticket))->assertOk();
    }

    public function test_both_admin_and_support_roles_reach_the_operator_queue(): void
    {
        $this->actingAs($this->user('admin'))->get(route('admin.support.tickets.index'))->assertOk();
        $this->actingAs($this->user('support'))->get(route('admin.support.tickets.index'))->assertOk();
        $this->actingAs($this->user('participant'))->get(route('admin.support.tickets.index'))->assertForbidden();
    }

    public function test_operator_reply_moves_new_to_in_progress_and_assigns(): void
    {
        $operator = $this->user('support');
        $ticket = SupportTicket::create([
            'user_id' => $this->user()->id, 'category_id' => $this->category()->id,
            'subject' => 'Вопрос', 'description' => 'Текст', 'status' => SupportTicketStatus::New,
        ]);

        $this->actingAs($operator)->post(route('admin.support.tickets.reply', $ticket), [
            'content' => 'Здравствуйте, проверяем.',
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertSame(SupportTicketStatus::InProgress, $ticket->status);
        $this->assertNotNull($ticket->first_responded_at);
        $this->assertSame($operator->id, $ticket->assigned_to);
    }

    public function test_internal_note_does_not_change_status_and_is_hidden_from_the_user(): void
    {
        $owner = $this->user();
        $operator = $this->user('admin');
        $ticket = SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $this->category()->id,
            'subject' => 'Вопрос', 'description' => 'Текст', 'status' => SupportTicketStatus::New,
        ]);

        $this->actingAs($operator)->post(route('admin.support.tickets.reply', $ticket), [
            'content'     => 'Позвонил в банк, ждём ответа',
            'is_internal' => '1',
        ]);

        $ticket->refresh();
        $this->assertSame(SupportTicketStatus::New, $ticket->status);
        $this->assertNull($ticket->first_responded_at);

        $this->actingAs($owner)->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('Позвонил в банк');
    }

    public function test_user_reply_reopens_a_ticket_waiting_on_them(): void
    {
        $owner = $this->user();
        $ticket = SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $this->category()->id,
            'subject' => 'Вопрос', 'description' => 'Текст',
            'status'  => SupportTicketStatus::NeedsClarification,
        ]);

        $this->actingAs($owner)->post(route('tickets.comment', $ticket), [
            'content' => 'Вот номер заявки: 123',
        ])->assertRedirect();

        $this->assertSame(SupportTicketStatus::InProgress, $ticket->refresh()->status);
    }

    public function test_illegal_status_transition_is_rejected(): void
    {
        $operator = $this->user('admin');
        $ticket = SupportTicket::create([
            'user_id' => $this->user()->id, 'category_id' => $this->category()->id,
            'subject' => 'Вопрос', 'description' => 'Текст', 'status' => SupportTicketStatus::New,
        ]);

        // New → Resolved is not allowed; it has to pass through In Progress.
        $this->actingAs($operator)
            ->patch(route('admin.support.tickets.status', $ticket), ['status' => 'resolved'])
            ->assertSessionHas('error');

        $this->assertSame(SupportTicketStatus::New, $ticket->refresh()->status);
    }

    public function test_user_confirms_resolution_and_ticket_closes(): void
    {
        $owner = $this->user();
        $ticket = SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $this->category()->id,
            'subject' => 'Вопрос', 'description' => 'Текст',
            'status'  => SupportTicketStatus::Resolved, 'resolved_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('tickets.confirm', $ticket))->assertRedirect();

        $ticket->refresh();
        $this->assertSame(SupportTicketStatus::Closed, $ticket->status);
        $this->assertSame(TicketCloseReason::UserConfirmed, $ticket->closed_reason);
        $this->assertNotNull($ticket->closed_at);
    }

    public function test_rating_is_accepted_when_resolved_and_refused_when_new(): void
    {
        $owner = $this->user();
        $category = $this->category();

        $resolved = SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $category->id,
            'subject' => 'A', 'description' => 'B',
            'status'  => SupportTicketStatus::Resolved, 'resolved_at' => now(),
        ]);
        $this->actingAs($owner)->post(route('tickets.rate', $resolved), ['csat_score' => 5]);
        $this->assertSame(5, $resolved->refresh()->csat_score);

        $fresh = SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $category->id,
            'subject' => 'A', 'description' => 'B', 'status' => SupportTicketStatus::New,
        ]);
        $this->actingAs($owner)->post(route('tickets.rate', $fresh), ['csat_score' => 5])
            ->assertSessionHas('error');
        $this->assertNull($fresh->refresh()->csat_score);
    }

    public function test_open_filter_hides_closed_tickets_by_default(): void
    {
        $owner = $this->user();
        $category = $this->category();

        SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $category->id,
            'subject' => 'Открытая заявка', 'description' => 'B', 'status' => SupportTicketStatus::New,
        ]);
        SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $category->id,
            'subject' => 'Закрытая заявка', 'description' => 'B', 'status' => SupportTicketStatus::Closed,
        ]);

        $this->actingAs($owner)->get(route('tickets.index'))
            ->assertSee('Открытая заявка')->assertDontSee('Закрытая заявка');

        $this->actingAs($owner)->get(route('tickets.index', ['all' => 1]))
            ->assertSee('Открытая заявка')->assertSee('Закрытая заявка');
    }
}
