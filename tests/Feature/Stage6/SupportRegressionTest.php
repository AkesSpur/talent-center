<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Enums\TicketCloseReason;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regressions found in review. Each test fails against the pre-review code.
 */
class SupportRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name = 'Оплата'): SupportCategory
    {
        return SupportCategory::create(['name' => $name, 'is_active' => true]);
    }

    private function ticket(User $owner, SupportCategory $category, array $attrs = []): SupportTicket
    {
        return SupportTicket::create(array_merge([
            'user_id'     => $owner->id,
            'category_id' => $category->id,
            'subject'     => 'Тема',
            'description' => 'Описание',
            'status'      => SupportTicketStatus::New,
        ], $attrs));
    }

    public function test_ticket_pages_survive_a_deleted_user_account(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticket($owner, $this->category());

        $this->actingAs($operator)->post(route('admin.support.tickets.reply', $ticket), [
            'content' => 'Ответ поддержки',
        ]);

        // The participant closes their account; the ticket survives with user_id = null.
        $owner->delete();

        $this->actingAs($operator)->get(route('admin.support.tickets.show', $ticket))->assertOk();
    }

    public function test_editing_an_archived_category_does_not_reactivate_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = SupportCategory::create(['name' => 'Старое', 'is_active' => false]);

        $this->actingAs($admin)->put(route('admin.support.categories.update', $category), [
            'name'       => 'Старое (переименовано)',
            'sort_order' => 5,
            // no is_active key at all — an unchecked checkbox sends nothing
        ])->assertRedirect();

        $this->assertFalse($category->refresh()->is_active);
        $this->assertSame('Старое (переименовано)', $category->name);
    }

    public function test_user_can_still_rate_after_confirming_the_resolution(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticket($owner, $this->category(), [
            'status' => SupportTicketStatus::Resolved, 'resolved_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('tickets.confirm', $ticket));
        $this->assertSame(SupportTicketStatus::Closed, $ticket->refresh()->status);

        $this->actingAs($owner)->post(route('tickets.rate', $ticket), ['csat_score' => 4])
            ->assertSessionHasNoErrors();

        $this->assertSame(4, $ticket->refresh()->csat_score);
    }

    /** Changed after the end-to-end test (L03): a closed ticket was always resolved first, so it stays rateable. */
    public function test_user_can_rate_an_auto_closed_ticket(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticket($owner, $this->category(), [
            'status'        => SupportTicketStatus::Closed,
            'resolved_at'   => now()->subDays(4),
            'closed_at'     => now()->subDay(),
            'closed_reason' => TicketCloseReason::AutoInactivity,
        ]);

        $this->actingAs($owner)->post(route('tickets.rate', $ticket), ['csat_score' => 5])
            ->assertSessionHasNoErrors();

        $this->assertSame(5, $ticket->refresh()->csat_score);
    }

    public function test_operator_cannot_post_through_the_user_comment_route(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticket($owner, $this->category());

        $this->actingAs($operator)->post(route('tickets.comment', $ticket), [
            'content' => 'Ответ не тем путём',
        ])->assertForbidden();

        $this->assertSame(0, $ticket->comments()->count());
    }

    public function test_operator_cannot_reply_to_a_closed_ticket(): void
    {
        $operator = User::factory()->create(['role' => 'support']);
        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticket($owner, $this->category(), [
            'status' => SupportTicketStatus::Closed, 'closed_at' => now(),
        ]);

        $this->actingAs($operator)->post(route('admin.support.tickets.reply', $ticket), [
            'content' => 'Поздний ответ',
        ])->assertSessionHas('error');

        $this->assertSame(0, $ticket->comments()->count());
    }

    public function test_subcategory_must_belong_to_the_chosen_category(): void
    {
        $operator = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'participant']);

        $payments = $this->category('Оплата');
        $technical = $this->category('Технические');
        $foreignSub = SupportCategory::create([
            'name' => 'Не загружается файл', 'parent_id' => $technical->id, 'is_active' => true,
        ]);

        $ticket = $this->ticket($owner, $payments);

        $this->actingAs($operator)->patch(route('admin.support.tickets.category', $ticket), [
            'category_id'    => $payments->id,
            'subcategory_id' => $foreignSub->id,
        ])->assertSessionHasErrors('subcategory_id');

        $this->assertNull($ticket->refresh()->subcategory_id);
    }

    public function test_a_subcategory_cannot_become_a_ticket_main_category(): void
    {
        $operator = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'participant']);

        $parent = $this->category('Оплата');
        $sub = SupportCategory::create(['name' => 'Возврат', 'parent_id' => $parent->id, 'is_active' => true]);
        $ticket = $this->ticket($owner, $parent);

        $this->actingAs($operator)->patch(route('admin.support.tickets.category', $ticket), [
            'category_id' => $sub->id,
        ])->assertSessionHasErrors('category_id');

        $this->assertSame($parent->id, $ticket->refresh()->category_id);
    }

    public function test_valid_category_change_is_applied(): void
    {
        $operator = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'participant']);

        $parent = $this->category('Оплата');
        $sub = SupportCategory::create(['name' => 'Возврат', 'parent_id' => $parent->id, 'is_active' => true]);
        $ticket = $this->ticket($owner, $parent);

        $this->actingAs($operator)->patch(route('admin.support.tickets.category', $ticket), [
            'category_id'    => $parent->id,
            'subcategory_id' => $sub->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame($sub->id, $ticket->refresh()->subcategory_id);
    }

    public function test_category_with_children_cannot_be_nested_under_another(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $a = $this->category('A');
        $b = $this->category('B');
        SupportCategory::create(['name' => 'B-sub', 'parent_id' => $b->id, 'is_active' => true]);

        $this->actingAs($admin)->put(route('admin.support.categories.update', $b), [
            'name'      => 'B',
            'parent_id' => $a->id,
            'is_active' => 1,
        ])->assertSessionHasErrors('parent_id');

        $this->assertNull($b->refresh()->parent_id);
    }

    public function test_resolved_ticket_reopens_when_the_user_replies(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticket($owner, $this->category(), [
            'status' => SupportTicketStatus::Resolved, 'resolved_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('tickets.comment', $ticket), [
            'content' => 'Не помогло',
        ]);

        $ticket->refresh();
        $this->assertSame(SupportTicketStatus::InProgress, $ticket->status);
        $this->assertNull($ticket->resolved_at);
    }

    public function test_participant_cannot_reach_mutating_operator_routes(): void
    {
        $participant = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticket(
            User::factory()->create(['role' => 'participant']),
            $this->category(),
        );

        $this->actingAs($participant)
            ->post(route('admin.support.tickets.reply', $ticket), ['content' => 'x'])->assertForbidden();
        $this->actingAs($participant)
            ->patch(route('admin.support.tickets.status', $ticket), ['status' => 'in_progress'])->assertForbidden();
        $this->actingAs($participant)
            ->post(route('admin.support.tickets.assign', $ticket))->assertForbidden();
        $this->actingAs($participant)
            ->post(route('admin.support.categories.store'), ['name' => 'Хак'])->assertForbidden();
    }

    public function test_operator_creates_a_ticket_on_behalf_of_a_user(): void
    {
        $operator = User::factory()->create(['role' => 'support']);
        $target = User::factory()->create(['role' => 'participant']);
        $category = $this->category();

        $this->actingAs($operator)->post(route('admin.support.tickets.store'), [
            'user_id'     => $target->id,
            'category_id' => $category->id,
            'subject'     => 'Звонил по телефону',
            'description' => 'Не может войти в личный кабинет',
        ])->assertRedirect();

        $ticket = SupportTicket::first();
        $this->assertSame($target->id, $ticket->user_id);
        $this->assertSame(SupportTicketStatus::New, $ticket->status);

        // The ticket belongs to the user it was logged for, so they can see it.
        $this->actingAs($target)->get(route('tickets.show', $ticket))->assertOk();
    }

    public function test_upload_limits_are_enforced(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $category = $this->category();

        $this->actingAs($user)->post(route('tickets.store'), [
            'category_id' => $category->id,
            'subject'     => 'Много файлов',
            'description' => 'Текст',
            'files'       => array_map(
                fn () => \Illuminate\Http\UploadedFile::fake()->image('a.png'),
                range(1, 6),
            ),
        ])->assertSessionHasErrors('files');

        $this->actingAs($user)->post(route('tickets.store'), [
            'category_id' => $category->id,
            'subject'     => 'Опасный файл',
            'description' => 'Текст',
            'files'       => [\Illuminate\Http\UploadedFile::fake()->create('evil.svg', 10, 'image/svg+xml')],
        ])->assertSessionHasErrors('files.0');

        $this->assertSame(0, SupportTicket::count());
    }
}
