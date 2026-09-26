<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\SupportTicketStatus;
use App\Models\SupportCategory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/** CSV export of the ticket queue (ТЗ 9.2). */
class SupportTicketExportTest extends TestCase
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
        return SupportTicket::create(array_merge([
            'user_id'     => User::factory()->create(['role' => 'participant'])->id,
            'category_id' => $this->category->id,
            'subject'     => 'Не приходит диплом',
            'description' => 'Текст обращения.',
            'status'      => SupportTicketStatus::New,
        ], $attributes));
    }

    /** @param array<string, mixed> $query */
    private function csv(array $query = []): string
    {
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.support.tickets.export', $query));

        $response->assertOk();

        return $response->streamedContent();
    }

    public function test_it_downloads_a_utf8_csv_excel_can_read(): void
    {
        $this->ticket();

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.support.tickets.export'));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload();

        $body = $response->streamedContent();

        // Without the BOM Excel renders Cyrillic as mojibake.
        $this->assertSame("\xEF\xBB\xBF", substr($body, 0, 3));
        // Russian Excel splits on ';', not ','.
        $this->assertStringContainsString('Номер;Тема;Статус', $body);
        $this->assertStringContainsString('Не приходит диплом', $body);
    }

    public function test_a_participant_cannot_export(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'participant']))
            ->get(route('admin.support.tickets.export'))
            ->assertForbidden();
    }

    public function test_it_honours_the_status_filter(): void
    {
        $this->ticket(['subject' => 'НОВАЯ ЗАЯВКА', 'status' => SupportTicketStatus::New]);
        $this->ticket(['subject' => 'РЕШЁННАЯ ЗАЯВКА', 'status' => SupportTicketStatus::Resolved, 'resolved_at' => now()]);

        $body = $this->csv(['status' => 'resolved']);

        $this->assertStringContainsString('РЕШЁННАЯ ЗАЯВКА', $body);
        $this->assertStringNotContainsString('НОВАЯ ЗАЯВКА', $body);
    }

    public function test_it_honours_the_category_assignee_and_overdue_filters(): void
    {
        $other = SupportCategory::create(['name' => 'Конкурсы', 'is_active' => true]);
        $operator = User::factory()->create(['role' => 'support']);

        $this->ticket(['subject' => 'ПЕРВАЯ']);
        $this->ticket(['subject' => 'ВТОРАЯ', 'category_id' => $other->id]);
        $this->ticket(['subject' => 'ТРЕТЬЯ', 'assigned_to' => $operator->id]);
        $this->ticket([
            'subject'      => 'ЧЕТВЁРТАЯ',
            'status'       => SupportTicketStatus::InProgress,
            'sla_deadline' => now()->subHour(),
        ]);

        $byCategory = $this->csv(['category' => (string) $other->id]);
        $this->assertStringContainsString('ВТОРАЯ', $byCategory);
        $this->assertStringNotContainsString('ПЕРВАЯ', $byCategory);

        $byAssignee = $this->csv(['assignee' => (string) $operator->id]);
        $this->assertStringContainsString('ТРЕТЬЯ', $byAssignee);
        $this->assertStringNotContainsString('ПЕРВАЯ', $byAssignee);

        $unassigned = $this->csv(['assignee' => 'none']);
        $this->assertStringContainsString('ПЕРВАЯ', $unassigned);
        $this->assertStringNotContainsString('ТРЕТЬЯ', $unassigned);

        $overdue = $this->csv(['overdue' => '1']);
        $this->assertStringContainsString('ЧЕТВЁРТАЯ', $overdue);
        $this->assertStringNotContainsString('ПЕРВАЯ', $overdue);
    }

    public function test_an_empty_filter_value_means_no_filter(): void
    {
        // Every «Все» option submits an empty string.
        $this->ticket(['subject' => 'ВИДНА ВСЕГДА']);

        $this->assertStringContainsString(
            'ВИДНА ВСЕГДА',
            $this->csv(['status' => '', 'category' => '', 'assignee' => '']),
        );
    }

    public function test_a_subject_that_looks_like_a_formula_is_defused(): void
    {
        // Excel executes a cell beginning with =, +, - or @.
        $this->ticket(['subject' => '=1+1']);

        $this->assertStringContainsString("'=1+1", $this->csv());
    }

    public function test_every_ticket_appears_exactly_once(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->ticket(['subject' => "ЗАЯВКА-{$i}"]);
        }

        $body = $this->csv(['sort' => 'status', 'direction' => 'asc']);

        for ($i = 0; $i < 12; $i++) {
            $this->assertSame(1, substr_count($body, "ЗАЯВКА-{$i};"), "ЗАЯВКА-{$i} should appear once");
        }
    }

    public function test_the_queue_is_totally_ordered_so_chunked_reads_cannot_repeat_rows(): void
    {
        // The export reads in chunks of 500. «status» has five distinct values
        // across the whole table, so without a tie-break LIMIT/OFFSET would
        // repeat and drop rows once the queue outgrows one chunk — a defect no
        // realistically-sized fixture would surface.
        $controller = app(\App\Http\Controllers\Admin\Support\SupportTicketController::class);
        $queue = (new \ReflectionMethod($controller, 'queue'))
            ->invoke($controller, Request::create('/', 'GET', ['sort' => 'status']));

        $this->assertStringContainsString(
            'order by "status" desc, "id" desc',
            $queue->toSql(),
            'the queue must be totally ordered',
        );
    }
}
