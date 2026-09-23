<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Enums\TicketCloseReason;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportAttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fixes for the helpdesk end-to-end test report (docs/support-test-report).
 * Each test names the report check it covers.
 */
class SupportTestReportFixesTest extends TestCase
{
    use RefreshDatabase;

    private const XHR = ['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('support');
    }

    private function category(): SupportCategory
    {
        return SupportCategory::create(['name' => 'Технические вопросы', 'is_active' => true]);
    }

    private function ticket(User $owner, array $attrs = []): SupportTicket
    {
        return SupportTicket::create(array_merge([
            'user_id'     => $owner->id,
            'category_id' => $this->category()->id,
            'subject'     => 'Тема',
            'description' => 'Описание',
            'status'      => SupportTicketStatus::InProgress,
        ], $attrs));
    }

    private function exe(): UploadedFile
    {
        return UploadedFile::fake()->create('virus.exe', 4, 'application/x-msdownload');
    }

    // ── U10–U13, U19: a refused file no longer crashes the page ──

    public function test_refused_file_on_the_new_ticket_form_shows_a_message_and_keeps_the_text(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $this->actingAs($user)->from(route('tickets.create'))
            ->followingRedirects()
            ->post(route('tickets.store'), [
                'category_id' => $this->category()->id,
                'subject'     => 'Не проходит оплата',
                'description' => 'Приложил файл',
                'files'       => [$this->exe()],
            ])
            ->assertOk()
            ->assertSee('Файл «virus.exe»: такой формат прикрепить нельзя.', false)
            ->assertSee('value="Не проходит оплата"', false);

        $this->assertSame(0, SupportTicket::count());
    }

    public function test_refused_file_in_the_user_reply_box_shows_a_message(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticket($user);

        $this->actingAs($user)->from(route('tickets.show', $ticket))
            ->followingRedirects()
            ->post(route('tickets.comment', $ticket), [
                'content' => 'Ещё один скриншот',
                'files'   => [$this->exe()],
            ])
            ->assertOk()
            ->assertSee('Файл «virus.exe»: такой формат прикрепить нельзя.', false)
            ->assertSee('Ещё один скриншот');

        $this->assertSame(0, $ticket->comments()->count());
    }

    // ── O07: the operator sees why a reply was refused ──

    public function test_operator_reply_with_a_refused_file_shows_the_reason(): void
    {
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticket(User::factory()->create(['role' => 'participant']));

        $this->actingAs($operator)->from(route('admin.support.tickets.show', $ticket))
            ->followingRedirects()
            ->post(route('admin.support.tickets.reply', $ticket), [
                'content' => 'Посмотрите инструкцию',
                'files'   => [$this->exe()],
            ])
            ->assertOk()
            ->assertSee('Файл «virus.exe»: такой формат прикрепить нельзя.', false)
            ->assertSee('Посмотрите инструкцию');

        $this->assertSame(0, $ticket->comments()->count());
    }

    // ── S02, S04, P02–P04: forms sent over XHR (progress bar, text kept) ──

    public function test_xhr_ticket_submission_returns_where_to_go_and_keeps_the_flash(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($user)->withHeaders(self::XHR)->post(route('tickets.store'), [
            'category_id' => $this->category()->id,
            'subject'     => 'Скриншот ошибки',
            'description' => 'Во вложении',
            'files'       => [UploadedFile::fake()->image('screen.png', 900, 560)],
        ]);

        $ticket = SupportTicket::firstOrFail();
        $response->assertOk()
            ->assertExactJson(['redirect' => route('tickets.show', $ticket)])
            ->assertSessionHas('status', 'ticket-created');
        $this->assertSame(1, $ticket->attachments()->count());
    }

    public function test_xhr_refusal_names_the_file_and_keeps_its_position(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($user)->withHeaders(self::XHR)->post(route('tickets.store'), [
            'category_id' => $this->category()->id,
            'subject'     => 'Два файла',
            'description' => 'Один лишний',
            'files'       => [UploadedFile::fake()->image('ok.png'), $this->exe()],
        ]);

        // The index lets the picker mark exactly that file in its list.
        $errors = $response->assertStatus(422)->json('errors');
        $this->assertArrayNotHasKey('files.0', $errors);
        $this->assertSame([
            'Файл «virus.exe»: такой формат прикрепить нельзя. Подходят ' . SupportAttachmentService::EXTENSIONS_TEXT . '.',
        ], $errors['files.1']);
        $this->assertSame(0, SupportTicket::count());
    }

    public function test_xhr_reply_lands_on_the_new_message(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticket($user);

        $response = $this->actingAs($user)->withHeaders(self::XHR)->post(route('tickets.comment', $ticket), [
            'content' => 'Спасибо, жду',
        ]);

        $comment = $ticket->comments()->firstOrFail();
        $response->assertOk()->assertExactJson([
            'redirect' => route('tickets.show', $ticket) . '#comment-' . $comment->id,
        ]);
    }

    public function test_xhr_reply_to_a_ticket_closed_meanwhile_is_refused_in_russian(): void
    {
        $operator = User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticket(User::factory()->create(['role' => 'participant']), [
            'status' => SupportTicketStatus::Closed, 'closed_at' => now(),
        ]);

        $this->actingAs($operator)->withHeaders(self::XHR)
            ->post(route('admin.support.tickets.reply', $ticket), ['content' => 'Поздний ответ'])
            ->assertStatus(409)
            ->assertJson(['message' => 'Заявка закрыта — отвечать в неё нельзя.']);
    }

    public function test_request_over_the_php_limit_gets_a_site_styled_413_page(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $tooBig = ['CONTENT_LENGTH' => (string) (1024 ** 3)];

        $this->actingAs($user)->call('POST', route('tickets.store'), [], [], [], $tooBig)
            ->assertStatus(413)
            ->assertSee('Файлы слишком большие')
            ->assertSee('Вернуться к форме');

        // The XHR path gets a status code it turns into an in-page message.
        $this->actingAs($user)->call('POST', route('tickets.store'), [], [], [], $tooBig + [
            'HTTP_ACCEPT' => 'application/json',
        ])->assertStatus(413);
    }

    public function test_picker_never_promises_more_than_php_accepts(): void
    {
        $upload = ini_parse_quantity((string) ini_get('upload_max_filesize'));
        $post = ini_parse_quantity((string) ini_get('post_max_size'));

        $this->assertLessThanOrEqual(10 * 1024 * 1024, SupportAttachmentService::maxFileBytes());
        $this->assertLessThanOrEqual(20 * 1024 * 1024, SupportAttachmentService::maxTotalBytes());

        if ($upload > 0) {
            $this->assertLessThanOrEqual($upload, SupportAttachmentService::maxFileBytes());
        }
        if ($post > 0) {
            $this->assertLessThan($post, SupportAttachmentService::maxTotalBytes());
        }
    }

    // ── U06, U16, U17, U18: previews ──

    public function test_images_open_inline_for_the_lightbox_and_other_files_stay_downloads(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $stranger = User::factory()->create(['role' => 'participant']);

        $this->actingAs($owner)->post(route('tickets.store'), [
            'category_id' => $this->category()->id,
            'subject'     => 'Фото и чек',
            'description' => 'Вложения',
            'files'       => [
                UploadedFile::fake()->image('photo-kamera.jpg', 1600, 1200),
                UploadedFile::fake()->create('chek-oplaty.pdf', 30, 'application/pdf'),
            ],
        ]);

        $ticket = SupportTicket::firstOrFail();
        $photo = $ticket->attachments()->where('mime_type', 'image/jpeg')->firstOrFail();
        $pdf = $ticket->attachments()->where('mime_type', 'application/pdf')->firstOrFail();

        $preview = $this->actingAs($owner)->get($photo->previewUrl());
        $preview->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringStartsWith('inline', $preview->headers->get('Content-Disposition'));

        // Not an image: no inline view, the download stays an attachment.
        $this->actingAs($owner)->get($pdf->previewUrl())->assertNotFound();
        $this->assertStringStartsWith('attachment', $this->actingAs($owner)->get($pdf->downloadUrl())
            ->headers->get('Content-Disposition'));

        // Same access rules as downloads.
        $this->actingAs($stranger)->get($photo->previewUrl())->assertForbidden();
        $this->actingAs($stranger)->get($photo->thumbnailUrl())->assertForbidden();

        // The conversation shows a thumbnail that opens the lightbox.
        $this->actingAs($owner)->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee($photo->thumbnailUrl(), false)
            ->assertSee('data-lightbox', false);
    }

    public function test_thumbnail_is_a_small_jpeg_made_once(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);

        $this->actingAs($owner)->post(route('tickets.store'), [
            'category_id' => $this->category()->id,
            'subject'     => 'Большое фото',
            'description' => 'Вложение',
            'files'       => [UploadedFile::fake()->image('big.png', 1920, 1080)],
        ]);

        $photo = SupportTicket::firstOrFail()->attachments()->firstOrFail();

        $response = $this->actingAs($owner)->get($photo->thumbnailUrl());
        $response->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        [$width, $height] = getimagesizefromstring($response->streamedContent());
        $this->assertSame(480, max($width, $height));
        $this->assertTrue(Storage::disk('support')->exists('thumbs/' . $photo->token . '.jpg'));
    }

    public function test_internal_note_images_stay_hidden_from_the_user(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'admin']);
        $ticket = $this->ticket($owner);

        $this->actingAs($operator)->post(route('admin.support.tickets.reply', $ticket), [
            'content'     => 'Скриншот из админки',
            'is_internal' => 1,
            'files'       => [UploadedFile::fake()->image('admin.png')],
        ]);

        $image = $ticket->comments()->firstOrFail()->attachments()->firstOrFail();

        $this->actingAs($owner)->get($image->previewUrl())->assertForbidden();
        $this->actingAs($owner)->get($image->thumbnailUrl())->assertForbidden();
        $this->actingAs($operator)->get($image->previewUrl())->assertOk();
    }

    // ── U15 and the picker/conversation mismatch: one size format everywhere ──

    public function test_file_sizes_read_the_same_everywhere(): void
    {
        $this->assertSame('49 Б', SupportAttachmentService::formatBytes(49));
        $this->assertSame('1 КБ', SupportAttachmentService::formatBytes(1024));
        $this->assertSame('93 КБ', SupportAttachmentService::formatBytes(95718));
        $this->assertSame('1 МБ', SupportAttachmentService::formatBytes(1048771));
        $this->assertSame('3,8 МБ', SupportAttachmentService::formatBytes(3969923));
        $this->assertSame('10,6 МБ', SupportAttachmentService::formatBytes(11064548));
        $this->assertSame('20 МБ', SupportAttachmentService::formatBytes(20 * 1024 * 1024));
    }

    // ── C12: «Настройки поддержки» are admin-only ──

    public function test_support_role_cannot_open_or_change_support_settings(): void
    {
        $support = User::factory()->create(['role' => 'support']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();

        $this->actingAs($support)->get(route('admin.support.categories.index'))->assertForbidden();
        $this->actingAs($support)->post(route('admin.support.settings.update'), ['support_sla_hours' => 1])->assertForbidden();
        $this->actingAs($support)->post(route('admin.support.categories.archive', $category))->assertForbidden();
        $this->assertTrue($category->refresh()->is_active);

        // The queue itself stays open to them, without the settings button.
        $this->actingAs($support)->get(route('admin.support.tickets.index'))
            ->assertOk()
            ->assertDontSee(route('admin.support.categories.index'));

        $this->actingAs($admin)->get(route('admin.support.categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.support.tickets.index'))
            ->assertSee(route('admin.support.categories.index'));
    }

    // ── C10: every settings message is Russian ──

    public function test_settings_validation_messages_are_russian(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.support.settings.update'), [
            'support_sla_hours' => 0,
            'support_email'     => 'не-почта',
        ])->assertSessionHasErrors([
            'support_sla_hours' => 'Срок SLA должен быть не меньше 1 часа.',
            'support_email'     => 'Укажите корректный email.',
        ]);

        $this->actingAs($admin)->post(route('admin.support.settings.update'), ['support_sla_hours' => 721])
            ->assertSessionHasErrors(['support_sla_hours' => 'Срок SLA должен быть не больше 720 часов (30 дней).']);

        $this->actingAs($admin)->post(route('admin.support.settings.update'), ['support_sla_hours' => 'сутки'])
            ->assertSessionHasErrors(['support_sla_hours' => 'Срок SLA — целое число часов.']);
    }

    // ── L03: a ticket closed by an operator can still be rated ──

    public function test_ticket_closed_by_an_operator_can_still_be_rated(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $operator = User::factory()->create(['role' => 'support']);
        $ticket = $this->ticket($owner, ['status' => SupportTicketStatus::Resolved, 'resolved_at' => now()]);

        $this->actingAs($operator)->patch(route('admin.support.tickets.status', $ticket), [
            'status' => SupportTicketStatus::Closed->value,
        ])->assertSessionHasNoErrors();
        $this->assertSame(TicketCloseReason::Other, $ticket->refresh()->closed_reason);

        $this->actingAs($owner)->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Оцените ответ')
            ->assertSee('Заявка закрыта сотрудником поддержки.');

        $this->actingAs($owner)->post(route('tickets.rate', $ticket), ['csat_score' => 4])
            ->assertSessionHasNoErrors();
        $this->assertSame(4, $ticket->refresh()->csat_score);
    }

    public function test_ticket_that_was_never_resolved_cannot_be_rated(): void
    {
        $owner = User::factory()->create(['role' => 'participant']);
        $ticket = $this->ticket($owner);

        $this->actingAs($owner)->post(route('tickets.rate', $ticket), ['csat_score' => 5])
            ->assertSessionHas('error');
        $this->assertNull($ticket->refresh()->csat_score);
    }

    // ── O01: the queue's first tile says what it counts ──

    public function test_open_tile_counts_every_open_ticket_and_splits_it_by_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'participant']);

        $this->ticket($owner, ['status' => SupportTicketStatus::New]);
        $this->ticket($owner, ['status' => SupportTicketStatus::New]);
        $this->ticket($owner, ['status' => SupportTicketStatus::InProgress]);
        $this->ticket($owner, ['status' => SupportTicketStatus::Resolved]);

        $this->actingAs($admin)->get(route('admin.support.tickets.index'))
            ->assertOk()
            ->assertSeeInOrder(['Открытые', '3', 'Новые:', '2', 'В работе:', '1', 'Ждут уточнения:', '0'], false);
    }
}
