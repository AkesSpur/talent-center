<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\KbArticleStatus;
use App\Models\KbArticle;
use App\Models\SupportCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The client's sixteen preset knowledge-base articles (ТЗ 8).
 *
 * Run it explicitly — it is deliberately not part of DatabaseSeeder, because
 * these are the client's own draft texts, not fixtures every environment wants:
 *
 *     php artisan db:seed --class=KbArticleSeeder --force
 *
 * Idempotent. Articles are matched on the slug derived from the title, so a
 * re-run updates the same rows: a link already pasted into a ticket reply keeps
 * working, and views_count is not reset.
 *
 * A re-run *overwrites* the title, text, excerpt and category — the file is the
 * source of truth for these sixteen. Anything an editor has since changed on the
 * site is lost, so warn them before running it a second time on a live site.
 * The publication date and the author are set once and then left alone.
 */
class KbArticleSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<int, array<string, mixed>> $articles */
        $articles = require __DIR__ . '/data/kb_articles.php';

        // Names, not ids: ids differ between environments. One query, then
        // matched in PHP, so sixteen articles do not mean thirty-two lookups.
        $categories = SupportCategory::get()->groupBy('name');
        $author = User::where('role', 'admin')->orderBy('id')->first();

        $created = 0;
        $updated = 0;
        $skipped = [];

        foreach ($articles as $data) {
            $category = $this->findCategory($categories, $data['category'], parentId: null);

            if ($category === null) {
                $skipped[] = "{$data['title']} — нет категории «{$data['category']}»";

                continue;
            }

            $subcategory = $this->findCategory($categories, $data['subcategory'], parentId: $category->id);

            if ($subcategory === null) {
                $skipped[] = "{$data['title']} — нет подкатегории «{$data['subcategory']}»";

                continue;
            }

            // Derived, not stored in the data file, so it can never drift from
            // what the admin form would have produced for the same title.
            $slug = Str::slug($data['title'], '-', 'ru');
            $article = KbArticle::firstOrNew(['slug' => $slug]);
            $exists = $article->exists;

            $article->fill([
                'title'          => $data['title'],
                'category_id'    => $category->id,
                'subcategory_id' => $subcategory->id,
                'excerpt'        => $data['excerpt'],
                // Sanitised here too: the seeder writes straight to the model,
                // bypassing the controller that would normally clean it.
                'content'        => clean(trim($data['content']), 'kb'),
                'status'         => KbArticleStatus::Published,
                'sort_order'     => $data['sort_order'],
            ]);

            $article->author_id ??= $author?->id;
            $article->published_at ??= now();
            $article->save();

            if ($exists) {
                $updated++;
            } else {
                $created++;
            }
        }

        $this->command?->info("База знаний: создано {$created}, обновлено {$updated}.");

        foreach ($skipped as $reason) {
            // Loud rather than silent: a missing category means an article the
            // client expects to see simply is not there.
            $this->command?->warn("Пропущено: {$reason}");
        }

        if ($skipped !== []) {
            $this->command?->warn('Сначала выполните: php artisan db:seed --class=SupportCategorySeeder');
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \Illuminate\Database\Eloquent\Collection<int, SupportCategory>>  $categories
     */
    private function findCategory($categories, string $name, ?int $parentId): ?SupportCategory
    {
        return $categories->get($name)?->first(
            fn (SupportCategory $category) => $category->parent_id === $parentId
        );
    }
}
