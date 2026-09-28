<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\KbArticleStatus;
use App\Models\KbArticle;
use App\Models\SupportAttachment;
use App\Models\SupportCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** The public knowledge base (ТЗ 8.6). */
class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    private SupportCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);
    }

    /** @param array<string, mixed> $attributes */
    private function article(array $attributes = []): KbArticle
    {
        return KbArticle::create(array_merge([
            'title'        => 'Как оплатить участие',
            'slug'         => KbArticle::uniqueSlug($attributes['title'] ?? 'Как оплатить участие'),
            'category_id'  => $this->category->id,
            'content'      => '<p>Оплатить можно картой на странице заявки.</p>',
            'status'       => KbArticleStatus::Published,
            'published_at' => now(),
        ], $attributes));
    }

    // ── Visibility ─────────────────────────────────────

    public function test_a_guest_can_read_a_published_article(): void
    {
        $article = $this->article();

        $this->get(route('knowledge-base.show', $article->slug))
            ->assertOk()
            ->assertSee('Как оплатить участие')
            ->assertSee('Оплатить можно картой', false);
    }

    public function test_a_guest_gets_404_for_a_draft_or_an_archived_article(): void
    {
        $draft = $this->article(['title' => 'Черновик', 'status' => KbArticleStatus::Draft, 'published_at' => null]);
        $archived = $this->article(['title' => 'Архивная', 'status' => KbArticleStatus::Archived]);

        $this->get(route('knowledge-base.show', $draft->slug))->assertNotFound();
        $this->get(route('knowledge-base.show', $archived->slug))->assertNotFound();
    }

    public function test_staff_may_preview_an_unpublished_article(): void
    {
        $draft = $this->article(['status' => KbArticleStatus::Draft, 'published_at' => null]);

        foreach (['admin', 'support'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('knowledge-base.show', $draft->slug))
                ->assertOk()
                ->assertSee('Статья ещё не опубликована');
        }

        // A participant is still just a reader.
        $this->actingAs(User::factory()->create(['role' => 'participant']))
            ->get(route('knowledge-base.show', $draft->slug))
            ->assertNotFound();
    }

    public function test_the_index_lists_only_categories_that_have_published_articles(): void
    {
        $empty = SupportCategory::create(['name' => 'Пустая категория', 'is_active' => true]);
        $drafts = SupportCategory::create(['name' => 'Только черновики', 'is_active' => true]);

        $this->article(['title' => 'Видимая статья']);
        $this->article([
            'title'        => 'Невидимая статья',
            'category_id'  => $drafts->id,
            'status'       => KbArticleStatus::Draft,
            'published_at' => null,
        ]);

        $this->get(route('knowledge-base.index'))
            ->assertOk()
            ->assertSee('Оплата')
            ->assertSee('Видимая статья')
            ->assertDontSee('Пустая категория')
            ->assertDontSee('Только черновики')
            ->assertDontSee('Невидимая статья');

        $this->assertModelExists($empty);
    }

    // ── Search ─────────────────────────────────────────

    public function test_search_matches_the_title_and_the_body_and_skips_unpublished(): void
    {
        $this->article(['title' => 'Возврат средств', 'content' => '<p>Ни при чём.</p>']);
        $this->article(['title' => 'Другая статья', 'content' => '<p>Здесь написано слово возврат в тексте.</p>']);
        $this->article([
            'title'        => 'Возврат в черновике',
            'status'       => KbArticleStatus::Draft,
            'published_at' => null,
        ]);

        $this->get(route('knowledge-base.search', ['q' => 'возврат']))
            ->assertOk()
            ->assertSee('Возврат средств')
            ->assertSee('Другая статья')
            ->assertDontSee('Возврат в черновике');
    }

    public function test_a_title_match_is_listed_before_a_body_only_match(): void
    {
        // Body-only match created first, so insertion order cannot be what ranks it.
        $this->article(['title' => 'Совсем другое', 'content' => '<p>Слово диплом внутри текста.</p>']);
        $this->article(['title' => 'Диплом не пришёл', 'content' => '<p>Ни при чём.</p>']);

        $body = $this->get(route('knowledge-base.search', ['q' => 'диплом']))->assertOk()->getContent();

        $this->assertLessThan(
            strpos($body, 'Совсем другое'),
            strpos($body, 'Диплом не пришёл'),
            'a title hit must outrank a body hit',
        );
    }

    public function test_search_finds_an_article_by_its_category_name(): void
    {
        $this->article(['title' => 'Куда смотреть', 'content' => '<p>Ни при чём.</p>']);

        // Nothing but the category says «оплата», and that is what was typed.
        $this->get(route('knowledge-base.search', ['q' => 'оплата']))
            ->assertOk()
            ->assertSee('Куда смотреть');
    }

    public function test_search_finds_an_article_by_its_subcategory_name(): void
    {
        $child = SupportCategory::create([
            'name' => 'Возврат средств', 'parent_id' => $this->category->id, 'is_active' => true,
        ]);

        $this->article(['title' => 'Куда смотреть', 'subcategory_id' => $child->id, 'content' => '<p>Ни при чём.</p>']);

        $this->get(route('knowledge-base.search', ['q' => 'возврат']))
            ->assertOk()
            ->assertSee('Куда смотреть');
    }

    public function test_search_finds_an_article_by_its_short_description(): void
    {
        $this->article([
            'title'   => 'Куда смотреть',
            'excerpt' => 'Сертификат участника скачивается отдельно от диплома.',
            'content' => '<p>Ни при чём.</p>',
        ]);

        $this->get(route('knowledge-base.search', ['q' => 'сертификат']))
            ->assertOk()
            ->assertSee('Куда смотреть');
    }

    public function test_every_word_has_to_match_but_the_order_does_not(): void
    {
        $this->article(['title' => 'Оплата участия', 'content' => '<p>После оплаты придёт диплом.</p>']);
        $this->article(['title' => 'Только про оплату', 'content' => '<p>Ни при чём.</p>']);

        // Both words appear, in neither the same order nor next to each other.
        $this->get(route('knowledge-base.search', ['q' => 'диплом оплата']))
            ->assertOk()
            ->assertSee('Оплата участия')
            ->assertDontSee('Только про оплату');
    }

    public function test_a_word_matches_its_other_grammatical_forms(): void
    {
        $this->article([
            'title'   => 'Сроки конкурса',
            'content' => '<p>Диплом придёт на почту. Файлы можно приложить.</p>',
        ]);

        // Each of these is written in a different case or number than the
        // article uses — all of them have to find it anyway.
        foreach (['конкурсы', 'конкурс', 'конкурсе', 'дипломы', 'почты', 'файл'] as $typed) {
            $body = $this->get(route('knowledge-base.search', ['q' => $typed]))->assertOk()->getContent();

            $this->assertStringContainsString(
                'Сроки конкурса',
                $body,
                "«{$typed}» should find «Сроки конкурса»",
            );
        }
    }

    public function test_a_short_or_latin_word_is_matched_as_typed(): void
    {
        $this->article(['title' => 'Формат файлов', 'content' => '<p>Принимаем PDF и DOCX.</p>']);

        // «pdf» must not be chopped into a prefix that matches half the library.
        $this->get(route('knowledge-base.search', ['q' => 'pdf']))->assertOk()->assertSee('Формат файлов');
        $this->get(route('knowledge-base.search', ['q' => 'xlsx']))->assertOk()->assertDontSee('Формат файлов');
    }

    public function test_a_query_of_only_punctuation_finds_nothing(): void
    {
        $this->article(['title' => 'Какая-то статья']);

        $this->get(route('knowledge-base.search', ['q' => '???']))
            ->assertOk()
            ->assertDontSee('Какая-то статья');
    }

    public function test_a_title_hit_outranks_a_category_only_hit(): void
    {
        // Same category, so both match «оплата» — but only one is about it.
        $this->article(['title' => 'Сроки проверки', 'content' => '<p>Ни при чём.</p>']);
        $this->article(['title' => 'Как пройти оплату', 'content' => '<p>Ни при чём.</p>']);

        $body = $this->get(route('knowledge-base.search', ['q' => 'оплата']))->assertOk()->getContent();

        $this->assertLessThan(
            strpos($body, 'Сроки проверки'),
            strpos($body, 'Как пройти оплату'),
            'the article about the term must come before one that merely shares its category',
        );
    }

    public function test_renaming_a_category_reindexes_its_articles(): void
    {
        $this->article(['title' => 'Куда смотреть', 'content' => '<p>Ни при чём.</p>']);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.support.categories.update', $this->category), [
                'name' => 'Финансовые вопросы', 'sort_order' => 0, 'is_active' => 1,
            ])
            ->assertRedirect();

        $this->get(route('knowledge-base.search', ['q' => 'финансовые']))
            ->assertOk()->assertSee('Куда смотреть');

        // ...and the name it no longer has stops matching.
        $this->get(route('knowledge-base.search', ['q' => 'оплата']))
            ->assertOk()->assertDontSee('Куда смотреть');
    }

    public function test_search_strips_tags_so_markup_is_never_a_hit(): void
    {
        $this->article(['title' => 'Обычная статья', 'content' => '<p class="ql-align-center">Текст.</p>']);

        $this->get(route('knowledge-base.search', ['q' => 'ql-align']))
            ->assertOk()
            ->assertDontSee('Обычная статья');
    }

    public function test_an_empty_query_asks_for_one_instead_of_listing_everything(): void
    {
        $this->article(['title' => 'Какая-то статья']);

        $this->get(route('knowledge-base.search', ['q' => '']))
            ->assertOk()
            ->assertDontSee('Какая-то статья');
    }

    // ── Reading counts ─────────────────────────────────

    public function test_a_read_is_counted_without_touching_the_updated_date(): void
    {
        $article = $this->article();

        KbArticle::withoutTimestamps(
            fn () => $article->forceFill(['updated_at' => now()->subMonth()])->saveQuietly()
        );
        $updatedAt = $article->fresh()->updated_at;

        $this->get(route('knowledge-base.show', $article->slug))->assertOk();

        $article->refresh();

        $this->assertSame(1, $article->views_count);
        // «Обновлена» in the admin list must keep meaning "last edited".
        $this->assertTrue($updatedAt->equalTo($article->updated_at), 'a read must not touch updated_at');
    }

    public function test_a_refresh_in_the_same_session_does_not_count_twice(): void
    {
        $article = $this->article();

        $this->get(route('knowledge-base.show', $article->slug))->assertOk();
        $this->get(route('knowledge-base.show', $article->slug))->assertOk();

        $this->assertSame(1, $article->fresh()->views_count);
    }

    public function test_previewing_a_draft_does_not_count_as_a_read(): void
    {
        $draft = $this->article(['status' => KbArticleStatus::Draft, 'published_at' => null]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('knowledge-base.show', $draft->slug))
            ->assertOk();

        $this->assertSame(0, $draft->fresh()->views_count);
    }

    // ── Tickets ────────────────────────────────────────

    public function test_the_ticket_form_suggests_the_knowledge_base_only_once_it_has_articles(): void
    {
        $user = User::factory()->create(['role' => 'participant']);

        // Sending someone to an empty library is worse than not mentioning it.
        $this->actingAs($user)->get(route('tickets.create'))
            ->assertOk()->assertDontSee('базе знаний');

        $this->article();

        $this->actingAs($user)->get(route('tickets.create'))
            ->assertOk()->assertSee('базе знаний');
    }

    public function test_the_create_ticket_button_carries_the_article_category(): void
    {
        $article = $this->article();

        $this->get(route('knowledge-base.show', $article->slug))
            ->assertOk()
            ->assertSee(route('tickets.create', ['category' => $this->category->id]), false);
    }

    // ── Attachments ────────────────────────────────────

    public function test_an_attachment_of_a_published_article_downloads_for_a_guest(): void
    {
        Storage::fake('support');

        $article = $this->article();
        $attachment = $this->attach($article, 'pravila.pdf');

        $response = $this->get(route('knowledge-base.attachment', $attachment->token));

        $response->assertOk()
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertDownload('pravila.pdf');
    }

    public function test_an_attachment_of_a_draft_is_404_for_a_guest(): void
    {
        Storage::fake('support');

        $draft = $this->article(['status' => KbArticleStatus::Draft, 'published_at' => null]);
        $attachment = $this->attach($draft, 'secret.pdf');

        $this->get(route('knowledge-base.attachment', $attachment->token))->assertNotFound();

        // ...but a colleague previewing the article can open it.
        $this->actingAs(User::factory()->create(['role' => 'support']))
            ->get(route('knowledge-base.attachment', $attachment->token))
            ->assertOk();
    }

    public function test_a_ticket_attachment_is_not_reachable_through_the_public_route(): void
    {
        Storage::fake('support');

        $ticket = \App\Models\SupportTicket::create([
            'user_id'     => User::factory()->create(['role' => 'participant'])->id,
            'category_id' => $this->category->id,
            'subject'     => 'Тема',
            'description' => 'Текст.',
        ]);

        $attachment = $this->attach($ticket, 'private.pdf');

        // The public action serves knowledge-base articles and nothing else.
        $this->get(route('knowledge-base.attachment', $attachment->token))->assertNotFound();
    }

    private function attach(\Illuminate\Database\Eloquent\Model $owner, string $name): SupportAttachment
    {
        $path = UploadedFile::fake()->create($name, 10, 'application/pdf')->store('kb', 'support');

        return $owner->attachments()->create([
            'disk'          => 'support',
            'path'          => $path,
            'original_name' => $name,
            'mime_type'     => 'application/pdf',
            'size_bytes'    => 10240,
        ]);
    }
}
