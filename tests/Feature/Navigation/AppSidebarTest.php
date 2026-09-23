<?php

declare(strict_types=1);

namespace Tests\Feature\Navigation;

use App\Enums\SupportTicketStatus;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_sees_only_participant_and_organizer_sections(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Участник конкурсов')
            ->assertSee('Организатор конкурсов')
            ->assertSee(route('tickets.index'), false)
            ->assertDontSee('Администрирование')
            ->assertDontSee(route('admin.dashboard'), false)
            ->assertDontSee(route('support.dashboard'), false);
    }

    public function test_admin_sees_the_administration_section_with_support_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Администрирование')
            ->assertSee(route('admin.payments.index'), false)
            ->assertSee(route('admin.support.tickets.index'), false)
            ->assertSee(route('admin.support.categories.index'), false)
            ->assertDontSee(route('support.dashboard'), false);
    }

    public function test_support_role_sees_the_support_section_but_not_admin_pages(): void
    {
        $support = User::factory()->create(['role' => 'support']);

        $this->actingAs($support)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('support.dashboard'), false)
            ->assertSee(route('admin.support.tickets.index'), false)
            ->assertDontSee(route('admin.payments.index'), false)
            ->assertDontSee(route('admin.support.categories.index'), false);
    }

    public function test_current_page_is_marked_active(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $html = $this->actingAs($user)->get(route('tickets.index'))->assertOk()->getContent();

        // Exactly one nav link is the current page, and it is the support link.
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertMatchesRegularExpression(
            '#href="' . preg_quote(route('tickets.index'), '#') . '"\s+aria-current="page"#',
            $html,
        );
    }

    public function test_badges_count_tickets_with_new_messages(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $support = User::factory()->create(['role' => 'support']);
        $category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);

        // Support replied and the user hasn't opened it; no operator has opened it either.
        SupportTicket::create([
            'user_id' => $user->id, 'category_id' => $category->id,
            'subject' => 'A', 'description' => 'B', 'status' => SupportTicketStatus::InProgress,
            'last_staff_message_at' => now(),
        ]);

        $this->actingAs($user)->get(route('dashboard'))->assertSee('title="Заявки с новыми сообщениями"', false);
        $this->actingAs($support)->get(route('dashboard'))->assertSee('title="Заявки с новыми сообщениями"', false);
    }

    public function test_every_role_can_narrow_the_docked_sidebar(): void
    {
        foreach (['participant', 'support', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('dashboard'))
                ->assertOk()
                // the top-bar button, and the saved choice applied before the first paint
                ->assertSee('@click="toggleCompact()"', false)
                ->assertSee('aria-label="Свернуть меню"', false)
                ->assertSee("localStorage.getItem('tc.sidebar.compact')", false);
        }
    }

    public function test_public_header_links_logged_in_users_to_their_cabinet(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $this->actingAs($user)->get(route('contests.index'))
            ->assertOk()
            ->assertSee('Личный кабинет')
            ->assertDontSee(route('admin.platform-categories.index'), false);
    }
}
