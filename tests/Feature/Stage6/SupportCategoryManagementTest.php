<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\SupportCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_preset_structure_and_is_idempotent(): void
    {
        $this->seed(SupportCategorySeeder::class);
        $this->seed(SupportCategorySeeder::class);

        $this->assertSame(5, SupportCategory::roots()->count());
        $this->assertSame(25, SupportCategory::whereNotNull('parent_id')->count());
    }

    public function test_category_with_tickets_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);

        SupportTicket::create([
            'user_id'     => User::factory()->create(['role' => 'participant'])->id,
            'category_id' => $category->id,
            'subject'     => 'A', 'description' => 'B', 'status' => SupportTicketStatus::New,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.support.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('support_categories', ['id' => $category->id]);
    }

    public function test_archiving_a_category_also_archives_its_subcategories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parent = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);
        $child = SupportCategory::create(['name' => 'Возврат', 'parent_id' => $parent->id, 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.support.categories.archive', $parent));

        $this->assertFalse($parent->refresh()->is_active);
        $this->assertFalse($child->refresh()->is_active);
    }

    public function test_archived_category_cannot_be_chosen_for_a_new_ticket(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $archived = SupportCategory::create(['name' => 'Старое', 'is_active' => false]);

        $this->actingAs($user)->post(route('tickets.store'), [
            'category_id' => $archived->id,
            'subject'     => 'A',
            'description' => 'B',
        ])->assertSessionHasErrors('category_id');

        $this->assertSame(0, SupportTicket::count());
    }

    public function test_sla_hours_setting_drives_the_deadline(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.support.settings.update'), [
            'support_sla_hours' => 24,
            'support_email'     => 'support@talant-centr.ru',
        ])->assertRedirect();

        $user = User::factory()->create(['role' => 'participant']);
        $category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);

        $this->actingAs($user)->post(route('tickets.store'), [
            'category_id' => $category->id,
            'subject'     => 'A',
            'description' => 'B',
        ]);

        $ticket = SupportTicket::first();
        $this->assertEqualsWithDelta(24, $ticket->created_at->diffInHours($ticket->sla_deadline), 0.01);
    }
}
