<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\KbArticleStatus;
use App\Models\ActionLog;
use App\Models\KbArticle;
use App\Models\SupportCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Managing knowledge-base articles in the admin area (ТЗ 8.4, 8.5, 8.9). */
class KbArticleAdminTest extends TestCase
{
    use RefreshDatabase;

    private SupportCategory $category;

    private SupportCategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = SupportCategory::create(['name' => 'Оплата', 'is_active' => true]);
        $this->subcategory = SupportCategory::create([
            'name' => 'Возврат средств', 'parent_id' => $this->category->id, 'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'Как оплатить участие в конкурсе',
            'category_id' => $this->category->id,
            'content'     => '<h2>Шаги</h2><p>Откройте заявку и нажмите «Оплатить».</p>',
            'status'      => KbArticleStatus::Draft->value,
            'sort_order'  => 0,
        ], $overrides);
    }

    /** @param array<string, mixed> $attributes */
    private function article(array $attributes = []): KbArticle
    {
        return KbArticle::create(array_merge([
            'title'       => 'Готовая статья',
            'slug'        => KbArticle::uniqueSlug($attributes['title'] ?? 'Готовая статья'),
            'category_id' => $this->category->id,
            'content'     => '<p>Текст.</p>',
            'status'      => KbArticleStatus::Draft,
        ], $attributes));
    }

    // ── Access ─────────────────────────────────────────

    public function test_only_admins_reach_the_article_list(): void
    {
        $this->actingAs($this->admin())->get(route('admin.support.articles.index'))->assertOk();

        foreach (['support', 'participant'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('admin.support.articles.index'))
                ->assertForbidden();
        }
    }

    public function test_operators_may_use_the_article_lookup_for_replies(): void
    {
        $this->article(['title' => 'Опубликованная', 'status' => KbArticleStatus::Published, 'published_at' => now()]);

        // ТЗ 8.7: support writes the replies, so it needs the lookup even
        // though the rest of the knowledge-base admin is admin-only.
        $this->actingAs(User::factory()->create(['role' => 'support']))
            ->getJson(route('admin.support.articles.search', ['q' => 'Опубликованная']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.title', 'Опубликованная');

        $this->actingAs(User::factory()->create(['role' => 'participant']))
            ->getJson(route('admin.support.articles.search', ['q' => 'Опубликованная']))
            ->assertForbidden();
    }

    public function test_the_lookup_never_offers_an_unpublished_article(): void
    {
        $this->article(['title' => 'Черновик про оплату']);

        $this->actingAs($this->admin())
            ->getJson(route('admin.support.articles.search', ['q' => 'оплату']))
            ->assertOk()
            ->assertJsonCount(0, 'results');
    }

    public function test_there_is_no_delete_route(): void
    {
        // ТЗ 8.9: archiving only, so links in old ticket replies never break.
        $this->assertFalse(
            collect(app('router')->getRoutes())->contains(
                fn ($route) => str_contains((string) $route->getName(), 'articles.destroy')
            ),
            'articles must not be deletable',
        );
    }

    // ── Creating ───────────────────────────────────────

    public function test_creating_an_article_transliterates_the_russian_title_into_the_slug(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.store'), $this->payload([
                'title' => 'Что делать, если диплом не пришёл',
            ]))
            ->assertRedirect();

        // Str::slug's default map folds ч and ш; the 'ru' map spells them out.
        $this->assertSame('chto-delat-esli-diplom-ne-prishyol', KbArticle::sole()->slug);
    }

    public function test_two_articles_with_the_same_title_get_different_slugs(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.support.articles.store'), $this->payload())->assertRedirect();
        $this->actingAs($admin)->post(route('admin.support.articles.store'), $this->payload())->assertRedirect();

        $slugs = KbArticle::pluck('slug');

        $this->assertCount(2, $slugs->unique(), 'slugs must stay unique');
        $this->assertTrue($slugs->contains(fn (string $slug) => str_ends_with($slug, '-2')));
    }

    public function test_the_body_is_sanitised_on_save_but_keeps_its_headings(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.store'), $this->payload([
                'content' => '<h2>Заголовок</h2><script>alert(1)</script>'
                    . '<p onclick="steal()" class="bg-red-500">Текст</p>'
                    . '<p><a href="javascript:alert(1)">клик</a></p>',
            ]))
            ->assertRedirect();

        $content = KbArticle::sole()->content;

        // The default purifier profile would have eaten the heading too.
        $this->assertStringContainsString('<h2>Заголовок</h2>', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onclick', $content);
        $this->assertStringNotContainsString('bg-red-500', $content);
        $this->assertStringNotContainsString('javascript:', $content);
    }

    public function test_a_base64_image_is_dropped_rather_than_stored_in_the_row(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.store'), $this->payload([
                'content' => '<p><img src="data:image/png;base64,iVBORw0KGgo="></p><p>Текст</p>',
            ]))
            ->assertRedirect();

        $this->assertStringNotContainsString('data:image', KbArticle::sole()->content);
    }

    public function test_the_searchable_copy_is_maintained_on_save(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.store'), $this->payload([
                'title'          => 'Заголовок',
                'excerpt'        => 'Краткое описание.',
                'subcategory_id' => $this->subcategory->id,
                'content'        => '<p>Первый абзац.</p><p>Второй абзац.</p>',
            ]))
            ->assertRedirect();

        $article = KbArticle::sole();

        // strip_tags() alone would glue the paragraphs into «абзац.Второй».
        $this->assertSame('Первый абзац. Второй абзац.', $article->content_text);

        // Everything the search looks through, lowercased: title, excerpt,
        // both category names, body.
        $this->assertSame(
            'заголовок краткое описание. оплата возврат средств первый абзац. второй абзац.',
            $article->search_text,
        );
    }

    public function test_an_empty_editor_is_refused(): void
    {
        // Quill posts «<p><br></p>» when nothing was typed.
        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.store'), $this->payload(['content' => '<p><br></p>']))
            ->assertSessionHasErrors('content');
    }

    public function test_a_subcategory_from_another_category_is_refused(): void
    {
        $other = SupportCategory::create(['name' => 'Конкурсы', 'is_active' => true]);
        $stranger = SupportCategory::create(['name' => 'Сроки', 'parent_id' => $other->id, 'is_active' => true]);

        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.store'), $this->payload(['subcategory_id' => $stranger->id]))
            ->assertSessionHasErrors('subcategory_id');

        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.store'), $this->payload(['subcategory_id' => $this->subcategory->id]))
            ->assertSessionHasNoErrors();
    }

    // ── Transitions ────────────────────────────────────

    public function test_publishing_stamps_the_date_only_the_first_time(): void
    {
        $article = $this->article();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.support.articles.status', $article), ['status' => 'published'])
            ->assertRedirect();

        $first = $article->fresh()->published_at;
        $this->assertNotNull($first);

        // Time has to move, or a second stamp would be the same value and this
        // would pass with the guard removed.
        $this->travel(3)->days();

        // Archive, then publish again: an old article must not look new.
        $this->actingAs($admin)->post(route('admin.support.articles.status', $article), ['status' => 'archived']);
        $this->actingAs($admin)->post(route('admin.support.articles.status', $article), ['status' => 'published']);

        $this->assertTrue(
            $first->equalTo($article->fresh()->published_at),
            'republishing must not move the publication date',
        );
    }

    public function test_every_transition_is_written_to_the_action_log(): void
    {
        $article = $this->article();

        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.status', $article), ['status' => 'published']);

        $log = ActionLog::where('action', 'kb.article.status_changed')->sole();

        $this->assertSame(KbArticle::class, $log->target_type);
        $this->assertSame('draft', $log->metadata['old']['status']);
        $this->assertSame('published', $log->metadata['new']['status']);
    }

    public function test_an_unknown_status_changes_nothing(): void
    {
        $article = $this->article();

        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.status', $article), ['status' => 'deleted'])
            ->assertRedirect();

        $this->assertSame(KbArticleStatus::Draft, $article->fresh()->status);
    }

    // ── Editing ────────────────────────────────────────

    public function test_a_published_articles_address_does_not_change_when_the_title_does(): void
    {
        $article = $this->article(['status' => KbArticleStatus::Published, 'published_at' => now()]);
        $slug = $article->slug;

        $this->actingAs($this->admin())
            ->put(route('admin.support.articles.update', $article), $this->payload([
                'title'  => 'Совершенно другой заголовок',
                'status' => KbArticleStatus::Published->value,
            ]))
            ->assertRedirect();

        // Operators have pasted this URL into replies already.
        $this->assertSame($slug, $article->fresh()->slug);
    }

    public function test_a_draft_still_follows_its_title(): void
    {
        $article = $this->article();

        $this->actingAs($this->admin())
            ->put(route('admin.support.articles.update', $article), $this->payload([
                'title' => 'Новый заголовок',
            ]))
            ->assertRedirect();

        $this->assertSame('novyy-zagolovok', $article->fresh()->slug);
    }

    public function test_an_explicit_address_is_kept(): void
    {
        $article = $this->article();

        $this->actingAs($this->admin())
            ->put(route('admin.support.articles.update', $article), $this->payload(['slug' => 'oplata-uchastiya']))
            ->assertRedirect();

        $this->assertSame('oplata-uchastiya', $article->fresh()->slug);
    }

    public function test_an_address_already_in_use_is_refused(): void
    {
        $taken = $this->article(['title' => 'Первая']);
        $article = $this->article(['title' => 'Вторая']);

        $this->actingAs($this->admin())
            ->put(route('admin.support.articles.update', $article), $this->payload(['slug' => $taken->slug]))
            ->assertSessionHasErrors('slug');
    }

    // ── Attachments ────────────────────────────────────

    public function test_an_article_accepts_up_to_ten_files_and_refuses_the_eleventh(): void
    {
        Storage::fake('support');

        $files = fn (int $count) => collect(range(1, $count))
            ->map(fn (int $i) => UploadedFile::fake()->create("file-{$i}.pdf", 10, 'application/pdf'))
            ->all();

        $this->actingAs($this->admin())
            ->post(route('admin.support.articles.store'), $this->payload() + ['files' => $files(10)])
            ->assertSessionHasNoErrors();

        $this->assertCount(10, KbArticle::sole()->attachments);

        // ТЗ 8.9 counts per article, so the next upload is already over.
        $this->actingAs($this->admin())
            ->put(route('admin.support.articles.update', KbArticle::sole()), $this->payload() + ['files' => $files(1)])
            ->assertSessionHasErrors('files');
    }

    public function test_removing_a_file_frees_its_slot_in_the_same_save(): void
    {
        Storage::fake('support');

        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.support.articles.store'), $this->payload() + [
            'files' => collect(range(1, 10))
                ->map(fn (int $i) => UploadedFile::fake()->create("file-{$i}.pdf", 10, 'application/pdf'))
                ->all(),
        ])->assertSessionHasNoErrors();

        $article = KbArticle::sole();
        $doomed = $article->attachments->first();

        $this->actingAs($admin)->put(route('admin.support.articles.update', $article), $this->payload() + [
            'remove' => [$doomed->token],
            'files'  => [UploadedFile::fake()->create('new.pdf', 10, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $article->refresh();

        $this->assertCount(10, $article->attachments);
        $this->assertFalse($article->attachments->contains('token', $doomed->token));
        Storage::disk('support')->assertMissing($doomed->path);
    }

    public function test_an_inline_image_is_stored_under_a_name_derived_from_its_real_type(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.support.articles.image'), [
                // A PNG that claims to be a .jpg: the stored name follows the
                // sniffed type, never the one the browser supplied.
                'image' => UploadedFile::fake()->image('screenshot.jpg')->mimeType('image/png'),
            ]);

        $response->assertOk()->assertJsonStructure(['url']);

        $url = $response->json('url');

        // Root-relative: the URL goes into stored article HTML, so it must not
        // carry today's domain.
        $this->assertStringStartsWith('/storage/kb/images/', $url);
        $this->assertStringEndsWith('.png', $url);
        Storage::disk('public')->assertExists('kb/images/' . basename($url));
    }

    public function test_a_file_renamed_as_an_image_is_refused(): void
    {
        Storage::fake('public');

        // A real file on disk, not `UploadedFile::fake()`: the fake reports a
        // mime type derived from the *name*, so it would prove nothing about
        // the sniffing this check depends on.
        $path = tempnam(sys_get_temp_dir(), 'kb-') . '.jpg';
        file_put_contents($path, '<?php echo "hi"; ?>');

        try {
            $this->actingAs($this->admin())
                ->postJson(route('admin.support.articles.image'), [
                    'image' => new UploadedFile($path, 'payload.jpg', 'image/jpeg', null, true),
                ])
                ->assertStatus(422);

            $this->assertEmpty(Storage::disk('public')->allFiles('kb/images'));
        } finally {
            @unlink($path);
        }
    }

    // ── Category cascade (ТЗ 8.9) ──────────────────────

    public function test_archiving_a_category_archives_its_articles_but_restoring_does_not(): void
    {
        $draft = $this->article(['title' => 'Черновик']);
        $published = $this->article([
            'title' => 'Опубликованная', 'status' => KbArticleStatus::Published, 'published_at' => now(),
        ]);
        $inSubcategory = $this->article([
            'title'          => 'В подкатегории',
            'subcategory_id' => $this->subcategory->id,
            'status'         => KbArticleStatus::Published,
            'published_at'   => now(),
        ]);

        $elsewhere = SupportCategory::create(['name' => 'Конкурсы', 'is_active' => true]);
        $untouched = $this->article([
            'title' => 'Чужая', 'category_id' => $elsewhere->id,
            'status' => KbArticleStatus::Published, 'published_at' => now(),
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.support.categories.archive', $this->category))
            ->assertRedirect();

        foreach ([$draft, $published, $inSubcategory] as $article) {
            $this->assertSame(KbArticleStatus::Archived, $article->fresh()->status, $article->title);
        }

        $this->assertSame(KbArticleStatus::Published, $untouched->fresh()->status);

        // Restoring the category is not a judgement that the articles are
        // still correct, so they stay archived.
        $this->actingAs($admin)->post(route('admin.support.categories.archive', $this->category));

        foreach ([$draft, $published, $inSubcategory] as $article) {
            $this->assertSame(KbArticleStatus::Archived, $article->fresh()->status);
        }
    }

    public function test_the_cascade_is_recorded_in_the_action_log(): void
    {
        $this->article(['status' => KbArticleStatus::Published, 'published_at' => now()]);

        $this->actingAs($this->admin())->post(route('admin.support.categories.archive', $this->category));

        $log = ActionLog::where('action', 'support.category.archived')->sole();

        $this->assertSame(1, $log->metadata['articles_archived']);
    }

    // ── Listing ────────────────────────────────────────

    public function test_the_list_filters_by_category_status_and_text(): void
    {
        $elsewhere = SupportCategory::create(['name' => 'Конкурсы', 'is_active' => true]);

        $this->article(['title' => 'ПЕРВАЯ', 'content' => '<p>Про оплату.</p>']);
        $this->article(['title' => 'ВТОРАЯ', 'category_id' => $elsewhere->id]);
        $this->article(['title' => 'ТРЕТЬЯ', 'status' => KbArticleStatus::Published, 'published_at' => now()]);

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.support.articles.index', ['category' => $this->category->id]))
            ->assertOk()->assertSee('ПЕРВАЯ')->assertDontSee('ВТОРАЯ');

        $this->actingAs($admin)->get(route('admin.support.articles.index', ['status' => 'published']))
            ->assertOk()->assertSee('ТРЕТЬЯ')->assertDontSee('ПЕРВАЯ');

        $this->actingAs($admin)->get(route('admin.support.articles.index', ['search' => 'оплату']))
            ->assertOk()->assertSee('ПЕРВАЯ')->assertDontSee('ВТОРАЯ');
    }

    public function test_a_subcategory_filter_finds_articles_filed_under_it(): void
    {
        $this->article(['title' => 'ПОДКАТЕГОРИЙНАЯ', 'subcategory_id' => $this->subcategory->id]);
        $this->article(['title' => 'ОБЫЧНАЯ']);

        $this->actingAs($this->admin())
            ->get(route('admin.support.articles.index', ['category' => $this->subcategory->id]))
            ->assertOk()
            ->assertSee('ПОДКАТЕГОРИЙНАЯ')
            ->assertDontSee('ОБЫЧНАЯ');
    }

    public function test_an_empty_filter_value_means_no_filter(): void
    {
        $this->article(['title' => 'ВИДНА ВСЕГДА']);

        $this->actingAs($this->admin())
            ->get(route('admin.support.articles.index', ['status' => '', 'category' => '', 'search' => '']))
            ->assertOk()
            ->assertSee('ВИДНА ВСЕГДА');
    }
}
