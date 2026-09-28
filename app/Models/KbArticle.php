<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KbArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A knowledge-base article (ТЗ 8). Articles share the ticket category tree, so
 * a user browsing for an answer and an operator filing a ticket organise the
 * same way. Articles are never deleted — only archived — because operators
 * paste links to them into replies that stay readable for years.
 */
class KbArticle extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'subcategory_id',
        'content',
        'excerpt',
        'status',
        'sort_order',
        'author_id',
        'updated_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => KbArticleStatus::class,
            'published_at' => 'datetime',
            'sort_order'   => 'integer',
            'views_count'  => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Both derived, never written by hand: `content_text` is what result
        // snippets read, `search_text` is what the search matches against.
        static::saving(function (self $article): void {
            if ($article->isDirty('content')) {
                $article->content_text = self::plainText((string) $article->content);
            }

            // The category name is part of the index, so a move between
            // categories changes it as surely as an edit to the text does.
            if ($article->isDirty(['content', 'title', 'excerpt', 'category_id', 'subcategory_id'])) {
                $article->search_text = $article->buildSearchText();
            }
        });
    }

    // ── Scopes ─────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', KbArticleStatus::Published);
    }

    /** ТЗ 8.6.1: the editor's order first, newest within it. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')
            ->orderByDesc('published_at')
            // A total order: without it, paging repeats rows when several
            // articles share a sort_order and a publication date.
            ->orderByDesc('id');
    }

    /**
     * Search over `search_text` — title, excerpt, category, subcategory and
     * body, all lowercased into one column by buildSearchText().
     *
     * Every word of the query has to appear somewhere, in any order and in any
     * of those fields: «оплата диплом» finds an article about payment that
     * mentions diplomas, even though neither word pair is adjacent. Words are
     * matched on their stem, so «конкурсы» finds «конкурса» (see stem()).
     *
     * Deliberately `LIKE`, not MySQL FULLTEXT: the tests run on SQLite, which
     * has no MATCH…AGAINST, so a fulltext branch would be the one path nothing
     * ever exercises. A helpdesk holds tens of articles, not millions. If the
     * library ever grows past a few thousand, add a fulltext index and switch
     * this one method — nothing else reads the column.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $terms = self::terms($term);

        if ($terms === []) {
            // Punctuation only. Matching nothing beats listing the library.
            return $query->whereRaw('1 = 0');
        }

        foreach ($terms as $word) {
            $query->where('search_text', 'like', '%' . self::escapeLike(self::stem($word)) . '%');
        }

        return $query;
    }

    /**
     * The query split into words. Anything that is not a letter or a digit
     * separates them, so «диплом, конкурс» and «диплом конкурс» are the same
     * search. Capped so a pasted paragraph cannot build a huge query.
     *
     * @return array<int, string>
     */
    public static function terms(string $query, int $limit = 6): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower(trim($query)), -1, PREG_SPLIT_NO_EMPTY);

        return array_slice($words ?: [], 0, $limit);
    }

    /**
     * A crude Russian stem: drop the inflectional ending so «конкурсы»,
     * «конкурса» and «конкурсе» all match the same articles. How much comes
     * off grows with the word, because longer words carry longer endings —
     * «почта/почту», «файл/файлов», «пользователь/пользователями» are all
     * interchangeable under this rule.
     *
     * Only for words that are entirely Cyrillic and long enough to survive it:
     * chopping «pdf» or «email» would match half the library.
     *
     * It over-matches sometimes — «диплом» stems to «дипл», which would also
     * find «дипломатия». That is the intended trade: an extra row in a list of
     * ten costs a glance, while «ничего не нашлось» for someone who typed the
     * plural costs a support ticket. It does not bridge parts of speech, so
     * «оплатить» will not find an article that only ever says «оплата».
     */
    public static function stem(string $word): string
    {
        $length = mb_strlen($word);

        if ($length < 5 || ! preg_match('/^\p{Cyrillic}+$/u', $word)) {
            return $word;
        }

        $drop = match (true) {
            $length >= 11 => 3,
            $length >= 6  => 2,
            default       => 1,
        };

        return mb_substr($word, 0, $length - $drop);
    }

    /** Everything the search looks through, lowercased into one column. */
    public function buildSearchText(): string
    {
        // Read fresh rather than from a loaded relation: this also runs right
        // after a category was renamed, when a cached relation would be stale.
        $categories = SupportCategory::whereIn('id', array_filter([$this->category_id, $this->subcategory_id]))
            ->pluck('name')
            ->all();

        $parts = array_filter([$this->title, $this->excerpt, ...$categories, $this->content_text]);

        return mb_strtolower(trim(implode(' ', $parts)));
    }

    /**
     * Re-derive `search_text` for every article filed under a category, after
     * that category has been renamed. Without this the old name keeps matching
     * and the new one never does.
     */
    public static function reindexCategory(SupportCategory $category): void
    {
        self::query()
            // Grouped: chunkById appends its own `where id > …`, and an
            // ungrouped orWhere would swallow it.
            ->where(fn (Builder $q) => $q->where('category_id', $category->id)
                ->orWhere('subcategory_id', $category->id))
            ->chunkById(200, function ($articles): void {
                $articles->each(fn (self $article) => self::withoutTimestamps(
                    fn () => $article->forceFill(['search_text' => $article->buildSearchText()])->saveQuietly()
                ));
            });
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }

    /**
     * Closest match first. Now that a hit can come from the category name or
     * the body, an article actually *about* what was typed has to outrank one
     * that merely shares a category with it.
     *
     * Sorted in PHP rather than SQL: an `order by … like …` would be subject
     * to the driver's collation, which is exactly what `search_text` exists to
     * avoid. Result sets here are capped at fifty rows.
     *
     * @param  \Illuminate\Support\Collection<int, self>  $articles
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function rank($articles, string $query)
    {
        $phrase = mb_strtolower(trim($query));
        $stems = array_map([self::class, 'stem'], self::terms($query));

        // PHP 8 sorts are stable, so the query's own order survives within
        // each group.
        return $articles->sortBy(function (self $article) use ($phrase, $stems): int {
            $title = mb_strtolower((string) $article->title);
            $excerpt = mb_strtolower((string) $article->excerpt);

            return match (true) {
                str_contains($title, $phrase)          => 0,  // the whole phrase, in the title
                self::containsAll($title, $stems)      => 1,  // every word, in the title
                self::containsAll($excerpt, $stems)    => 2,  // every word, in the summary
                self::containsAny($title, $stems)      => 3,  // some word, in the title
                default                                => 4,  // body or category only
            };
        })->values();
    }

    /** @param array<int, string> $stems */
    private static function containsAll(string $haystack, array $stems): bool
    {
        if ($stems === []) {
            return false;
        }

        foreach ($stems as $stem) {
            if (! str_contains($haystack, $stem)) {
                return false;
            }
        }

        return true;
    }

    /** @param array<int, string> $stems */
    private static function containsAny(string $haystack, array $stems): bool
    {
        foreach ($stems as $stem) {
            if (str_contains($haystack, $stem)) {
                return true;
            }
        }

        return false;
    }

    // ── Relationships ──────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'subcategory_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(SupportAttachment::class, 'attachable');
    }

    // ── Helpers ────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === KbArticleStatus::Published;
    }

    /** The public URL, used in ticket replies and in the article list. */
    public function publicUrl(): string
    {
        return route('knowledge-base.show', $this->slug);
    }

    /** The excerpt if the author wrote one, otherwise the opening of the text. */
    public function summary(int $length = 200): string
    {
        return $this->excerpt
            ?: Str::limit((string) $this->content_text, $length);
    }

    /**
     * ТЗ 8.6: count a read without touching `updated_at` — the admin list's
     * «обновлена» column must keep meaning "last edited", not "last opened".
     * A plain UPDATE also avoids a lost update between two concurrent readers.
     */
    public function recordView(): void
    {
        DB::table('kb_articles')->where('id', $this->id)->increment('views_count');
    }

    /**
     * Readable text for search and snippets. `strip_tags()` alone would glue
     * «<p>раз</p><p>два</p>» into «раздва» and leave entities behind.
     */
    public static function plainText(string $html): string
    {
        $spaced = str_replace('<', ' <', $html);
        $text = html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // A non-breaking space is not \s in every PCRE build.
        $text = str_replace("\u{00A0}", ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * A Latin slug derived from a Russian title. `Str::slug()`'s default map
     * folds ч, ж and щ onto bare letters («ЧаВо» → «cavo»); the 'ru' map
     * spells them out.
     */
    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = rtrim(Str::limit(Str::slug($source, '-', 'ru'), 180, ''), '-');

        if ($base === '') {
            $base = 'article';
        }

        $slug = $base;

        for ($suffix = 2; self::slugTaken($slug, $ignoreId); $suffix++) {
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }

    private static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        return self::where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }
}
