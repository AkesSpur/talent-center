<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Support;

use App\Enums\KbArticleStatus;
use App\Http\Controllers\Concerns\RespondsToUploadForms;
use App\Http\Controllers\Controller;
use App\Http\Requests\Support\StoreKbArticleRequest;
use App\Models\KbArticle;
use App\Models\SupportAttachment;
use App\Models\SupportCategory;
use App\Services\ActionLogService;
use App\Services\SupportAttachmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * «База знаний» in the admin area (ТЗ 8.5).
 *
 * Articles are never deleted, only archived: operators paste links to them
 * into ticket replies, and those replies stay readable for years.
 */
class KbArticleController extends Controller
{
    use RespondsToUploadForms;

    /** Where Quill's inline pictures live on the public disk. */
    private const IMAGE_FOLDER = 'kb/images';

    public function __construct(private readonly SupportAttachmentService $files) {}

    public function index(Request $request): View
    {
        $query = KbArticle::query()->with(['category', 'subcategory', 'updatedBy']);

        // Empty means «все» — every select on this form submits '' for that.
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // One select offers both levels, so a subcategory has to match either
        // column — an article stores the root in category_id and the child,
        // when there is one, in subcategory_id.
        if ($category = $request->query('category')) {
            $id = (int) $category;

            $query->where(fn (Builder $q) => $q->where('category_id', $id)->orWhere('subcategory_id', $id));
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->search($search);
        }

        $articles = $query->ordered()->paginate(20)->withQueryString();

        return view('admin.support.articles.index', [
            'articles'   => $articles,
            'categories' => $this->categoryTree(),
            'statuses'   => KbArticleStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.support.articles.create', [
            'categories' => $this->categoryTree(),
            'statuses'   => KbArticleStatus::cases(),
        ]);
    }

    public function store(StoreKbArticleRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $article = new KbArticle();
        $this->fill($article, $data);
        $article->author_id = $request->user()->id;
        $article->save();

        $this->syncAttachments($article, $request);

        ActionLogService::log('kb.article.created', $article, [
            'title'  => $article->title,
            'status' => $article->status->value,
        ]);

        return $this->uploadFormDone($request, route('admin.support.articles.edit', $article), [
            'status' => 'kb-article-created',
        ]);
    }

    public function edit(KbArticle $article): View
    {
        $article->load(['attachments', 'author', 'updatedBy']);

        return view('admin.support.articles.edit', [
            'article'    => $article,
            'categories' => $this->categoryTree(),
            'statuses'   => KbArticleStatus::cases(),
        ]);
    }

    public function update(StoreKbArticleRequest $request, KbArticle $article): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $before = $this->snapshot($article);

        $this->fill($article, $data);
        $article->updated_by = $request->user()->id;
        $article->save();

        $this->syncAttachments($article, $request);

        ActionLogService::log('kb.article.updated', $article, [
            'old' => $before,
            'new' => $this->snapshot($article),
        ]);

        return $this->uploadFormDone($request, route('admin.support.articles.edit', $article), [
            'status' => 'kb-article-updated',
        ]);
    }

    /**
     * The four transitions of ТЗ 8.4 as one action, so the list rows can offer
     * a single button each. There is deliberately no delete.
     */
    public function transition(Request $request, KbArticle $article): RedirectResponse
    {
        $target = KbArticleStatus::tryFrom((string) $request->input('status'));

        if ($target === null || $target === $article->status) {
            return back()->with('error', 'Неизвестный статус статьи.');
        }

        $was = $article->status;
        $article->status = $target;

        // ТЗ 8.4: the publication date is the first one; re-publishing after a
        // spell in the archive must not make an old article look new.
        if ($target === KbArticleStatus::Published && $article->published_at === null) {
            $article->published_at = now();
        }

        $article->updated_by = $request->user()->id;
        $article->save();

        ActionLogService::log('kb.article.status_changed', $article, [
            'old' => ['status' => $was->value],
            'new' => ['status' => $target->value],
        ]);

        return back()->with('status', 'kb-article-status-changed');
    }

    /**
     * Quill's picture uploads (ТЗ 8.5 «изображения»).
     *
     * Inline pictures go to the public disk so an <img src> can reach them
     * without a controller in the way. That is safe only because the file is
     * re-checked as a real raster image and renamed from the *sniffed* type —
     * an .html or .svg named .jpg never gets written.
     */
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:' . SupportAttachmentService::MAX_FILE_KB],
        ], [
            'image.image'    => 'Это не изображение.',
            'image.mimes'    => 'Подходят JPG, PNG, GIF и WebP.',
            'image.max'      => 'Изображение больше 10 МБ.',
            'image.required' => 'Файл не получен.',
        ]);

        $file = $request->file('image');
        $extension = match ($file->getMimeType()) {
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $path = $file->storeAs(
            self::IMAGE_FOLDER,
            bin2hex(random_bytes(16)) . '.' . $extension,
            'public',
        );

        // Root-relative, not asset(): this URL is written into the article's
        // stored HTML, and an absolute one would bake today's domain into
        // every article body.
        return response()->json(['url' => '/storage/' . $path]);
    }

    /**
     * Live search behind «Вставить статью» in the reply form (ТЗ 8.7).
     * Registered for operators as well as admins — they are the ones writing
     * the replies — so it returns published articles only.
     */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $articles = KbArticle::rank(
            KbArticle::published()->search($term)->ordered()->with('category')->limit(10)->get(),
            $term,
        );

        return response()->json([
            'results' => $articles->map(fn (KbArticle $article) => [
                'title'    => $article->title,
                'category' => $article->category?->name,
                'url'      => $article->publicUrl(),
            ])->all(),
        ]);
    }

    // ── Internals ──────────────────────────────────────

    /** @param array<string, mixed> $data */
    private function fill(KbArticle $article, array $data): void
    {
        $status = KbArticleStatus::from($data['status']);

        $article->fill([
            'title'          => $data['title'],
            'category_id'    => (int) $data['category_id'],
            'subcategory_id' => $data['subcategory_id'] ?? null,
            'excerpt'        => $data['excerpt'] ?? null,
            // Sanitised going in as well as coming out: a template that forgets
            // one of the two is then still not an XSS hole.
            'content'        => clean($data['content'], 'kb'),
            'status'         => $status,
            'sort_order'     => (int) ($data['sort_order'] ?? 0),
        ]);

        // An author may correct the address; left blank it follows the title,
        // but only while the article has never been published — a live URL in
        // someone's reply must not change under them.
        $slug = trim((string) ($data['slug'] ?? ''));

        if ($slug !== '') {
            $article->slug = KbArticle::uniqueSlug($slug, $article->id);
        } elseif ($article->slug === null || $article->published_at === null) {
            $article->slug = KbArticle::uniqueSlug($data['title'], $article->id);
        }

        if ($status === KbArticleStatus::Published && $article->published_at === null) {
            $article->published_at = now();
        }
    }

    private function syncAttachments(KbArticle $article, StoreKbArticleRequest $request): void
    {
        $removing = array_map('strval', (array) $request->input('remove', []));

        if ($removing !== []) {
            $article->attachments()->whereIn('token', $removing)->get()
                ->each(function (SupportAttachment $attachment): void {
                    Storage::disk($attachment->disk)->delete($attachment->path);
                    $attachment->delete();
                });
        }

        $files = $request->file('files') ?? [];

        if ($files !== []) {
            $this->files->attachMany($article, $files, 'kb/' . $article->id);
        }
    }

    /** @return array<string, mixed> */
    private function snapshot(KbArticle $article): array
    {
        return [
            'title'          => $article->title,
            'slug'           => $article->slug,
            'category_id'    => $article->category_id,
            'subcategory_id' => $article->subcategory_id,
            'status'         => $article->status->value,
            'sort_order'     => $article->sort_order,
        ];
    }

    /**
     * Top-level categories with their children, for the dependent selects.
     * Archived ones are kept: an article filed under a category that was later
     * archived must still be editable and must still show where it sits.
     */
    private function categoryTree(): Collection
    {
        return SupportCategory::roots()->ordered()
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->get();
    }
}
