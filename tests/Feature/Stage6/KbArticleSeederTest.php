<?php

declare(strict_types=1);

namespace Tests\Feature\Stage6;

use App\Enums\KbArticleStatus;
use App\Models\KbArticle;
use App\Models\SupportCategory;
use Database\Seeders\KbArticleSeeder;
use Database\Seeders\SupportCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The client's sixteen preset articles (ТЗ 8). */
class KbArticleSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedCategories(): void
    {
        $this->seed(SupportCategorySeeder::class);
    }

    public function test_it_seeds_every_article_published_and_filed_correctly(): void
    {
        $this->seedCategories();
        $this->seed(KbArticleSeeder::class);

        $articles = KbArticle::with(['category', 'subcategory'])->get();

        $this->assertCount(16, $articles);
        $this->assertCount(16, $articles->where('status', KbArticleStatus::Published));

        // Every article lands under a real top-level category and a
        // subcategory that belongs to it — the pair the public tree shows.
        foreach ($articles as $article) {
            $this->assertNotNull($article->category, $article->title);
            $this->assertNull($article->category->parent_id, $article->title);
            $this->assertNotNull($article->subcategory, $article->title);
            $this->assertSame($article->category_id, $article->subcategory->parent_id, $article->title);
            $this->assertNotNull($article->published_at, $article->title);
        }

        // All five categories of the draft are represented.
        $this->assertCount(5, $articles->pluck('category_id')->unique());
    }

    public function test_slugs_are_latin_and_unique(): void
    {
        $this->seedCategories();
        $this->seed(KbArticleSeeder::class);

        $slugs = KbArticle::pluck('slug');

        $this->assertCount(16, $slugs->unique(), 'slugs must not collide');

        foreach ($slugs as $slug) {
            // A percent-encoded Cyrillic slug in a URL would be unreadable in
            // the ticket replies these links get pasted into.
            $this->assertMatchesRegularExpression('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
        }
    }

    public function test_running_it_twice_updates_in_place(): void
    {
        $this->seedCategories();
        $this->seed(KbArticleSeeder::class);

        $article = KbArticle::where('slug', 'kak-oplatit-uchastie-v-konkurse')->sole();
        $article->recordView();
        $ids = KbArticle::pluck('id')->sort()->values();

        $this->seed(KbArticleSeeder::class);

        $this->assertCount(16, KbArticle::all(), 'a re-run must not duplicate');
        $this->assertEquals($ids, KbArticle::pluck('id')->sort()->values(), 'the same rows, so old links keep working');
        // Rebuilding the row would reset the counter and lose the history.
        $this->assertSame(1, $article->fresh()->views_count);
    }

    public function test_seeded_articles_are_findable_by_title_body_and_category(): void
    {
        $this->seedCategories();
        $this->seed(KbArticleSeeder::class);

        // The derived search index has to be built during the seed, or the
        // knowledge base ships looking empty to anyone who uses the search.
        $byTitle = $this->get(route('knowledge-base.search', ['q' => 'диплом']))->assertOk();
        $byTitle->assertSee('Как получить диплом и наградные документы');

        // «СБП» appears in one article's body and nowhere else.
        $this->get(route('knowledge-base.search', ['q' => 'СБП']))
            ->assertOk()
            ->assertSee('Как оплатить участие в конкурсе');

        // «Организационные вопросы» is a category name, not article text.
        $this->get(route('knowledge-base.search', ['q' => 'организационные']))
            ->assertOk()
            ->assertSee('Правила участия и требования к работам');
    }

    public function test_the_markup_survives_the_sanitiser(): void
    {
        $this->seedCategories();
        $this->seed(KbArticleSeeder::class);

        $article = KbArticle::where('slug', 'kak-oplatit-uchastie-v-konkurse')->sole();

        // The default purifier profile would have eaten the heading.
        $this->assertStringContainsString('<h2>Важно</h2>', $article->content);
        $this->assertStringContainsString('<ol>', $article->content);
        $this->assertStringContainsString('<strong>', $article->content);
    }

    public function test_an_article_is_skipped_when_its_category_is_missing(): void
    {
        // Only some categories exist — the rest of the tree was never seeded.
        $payments = SupportCategory::create(['name' => 'Оплата и возврат средств', 'is_active' => true]);
        SupportCategory::create(['name' => 'Статус оплаты', 'parent_id' => $payments->id, 'is_active' => true]);

        $this->seed(KbArticleSeeder::class);

        // The one article whose pair exists is in; nothing blew up over the rest.
        $this->assertSame(1, KbArticle::count());
        $this->assertSame('kak-oplatit-uchastie-v-konkurse', KbArticle::sole()->slug);
    }

    public function test_it_is_not_part_of_the_default_seeder_run(): void
    {
        // These are the client's draft texts, not fixtures every environment
        // wants appearing on its public site.
        $this->assertStringNotContainsString(
            'KbArticleSeeder',
            file_get_contents(database_path('seeders/DatabaseSeeder.php')),
        );
    }
}
