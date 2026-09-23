<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportAttachmentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_owner_and_operators_can_download_an_attachment(): void
    {
        Storage::fake('support');

        $owner = User::factory()->create(['role' => 'participant']);
        $stranger = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $category = SupportCategory::create(['name' => 'Тех. проблемы', 'is_active' => true]);

        $this->actingAs($owner)->post(route('tickets.store'), [
            'category_id' => $category->id,
            'subject'     => 'Файл',
            'description' => 'Смотрите вложение',
            'files'       => [UploadedFile::fake()->create('report.pdf', 20, 'application/pdf')],
        ]);

        $attachment = SupportTicket::first()->attachments()->first();
        $this->assertNotNull($attachment);

        $this->actingAs($owner)->get($attachment->downloadUrl())->assertOk();
        $this->actingAs($operator)->get($attachment->downloadUrl())->assertOk();
        $this->actingAs($stranger)->get($attachment->downloadUrl())->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_for_an_attachment(): void
    {
        Storage::fake('support');

        $owner = User::factory()->create(['role' => 'participant']);
        $category = SupportCategory::create(['name' => 'Тех. проблемы', 'is_active' => true]);

        $ticket = SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $category->id,
            'subject' => 'A', 'description' => 'B', 'status' => SupportTicketStatus::New,
        ]);

        $attachment = $ticket->attachments()->create([
            'token'         => (string) \Illuminate\Support\Str::ulid(),
            'disk'          => 'support',
            'path'          => 'tickets/1/file.pdf',
            'original_name' => 'file.pdf',
            'mime_type'     => 'application/pdf',
            'size_bytes'    => 1024,
        ]);

        // No actingAs here: an anonymous visitor must never reach the file.
        $this->get($attachment->downloadUrl())->assertRedirect(route('login'));
    }

    public function test_internal_note_attachment_is_not_downloadable_by_the_user(): void
    {
        Storage::fake('support');

        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'admin']);
        $category = SupportCategory::create(['name' => 'Тех. проблемы', 'is_active' => true]);

        $ticket = SupportTicket::create([
            'user_id' => $owner->id, 'category_id' => $category->id,
            'subject' => 'A', 'description' => 'B', 'status' => SupportTicketStatus::New,
        ]);

        $this->actingAs($operator)->post(route('admin.support.tickets.reply', $ticket), [
            'content'     => 'Внутренний документ',
            'is_internal' => '1',
            'files'       => [UploadedFile::fake()->create('internal.pdf', 10, 'application/pdf')],
        ]);

        $attachment = $ticket->comments()->first()->attachments()->first();

        $this->actingAs($operator)->get($attachment->downloadUrl())->assertOk();
        $this->actingAs($owner)->get($attachment->downloadUrl())->assertForbidden();
    }
}
