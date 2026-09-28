<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\SupportAttachment;
use App\Models\SupportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The public knowledge base (ТЗ 8.6).
 *
 * Open without logging in on purpose: this is also where someone who *cannot*
 * log in looks for help, so an auth wall here would lock out the people who
 * need it most.
 */
class KnowledgeBaseController extends Controller
{
    /**
     * Everything the list pages show, minus `content` — the article bodies are
     * the largest column in the table and no listing renders them.
     *
     * @var array<int, string>
     */
    private const LIST_COLUMNS = [
        'id', 'title', 'slug', 'excerpt', 'content_text',
        'category_id', 'subcategory_id', 'sort_order', 'published_at',
    ];

    /** How many recently-read articles a visitor's session remembers. */
    private const VIEW_MEMORY = 50;

    public function index(): View
    {
        $articles = KbArticle::published()
            ->select(self::LIST_COLUMNS)
            ->ordered()
            ->get()
            ->groupBy('category_id');

        // Only categories that actually have something to read (ТЗ 8.6).
        $categories = SupportCategory::roots()->ordered()
            ->whereIn('id', $articles->keys())
            ->get();

        return view('knowledge-base.index', [
            'categories' => $categories,
            'articles'   => $articles,
        ]);
    }

    public function search(Request $request): View
    {
        $term = trim((string) $request->query('q'));

        $results = $term === ''
            ? collect()
            : KbArticle::rank(
                KbArticle::published()
                    ->select(self::LIST_COLUMNS)
                    ->search($term)
                    ->ordered()
                    ->with('category')
                    ->limit(50)
                    ->get(),
                $term,
            );

        return view('knowledge-base.search', [
            'term'    => $term,
            'results' => $results,
        ]);
    }

    public function show(Request $request, KbArticle $article): View
    {
        $this->assertVisible($request, $article);

        $article->load(['category', 'subcategory', 'attachments']);

        $this->countView($request, $article);

        return view('knowledge-base.show', [
            'article' => $article,
            // Shown to staff previewing something the public cannot see yet.
            'preview' => ! $article->isPublished(),
        ]);
    }

    /**
     * Article attachments, unlike ticket attachments, are readable by anyone
     * who can read the article — so they need a route of their own rather than
     * the token-gated one, whose policy is written around ticket ownership.
     *
     * They are still served from the private disk through this action: forced
     * download with sniffing off, so a .doc can never be rendered as a page on
     * our own origin.
     */
    public function attachment(Request $request, string $token): StreamedResponse
    {
        $attachment = SupportAttachment::where('token', $token)->firstOrFail();
        $article = $attachment->attachable;

        abort_unless($article instanceof KbArticle, 404);
        $this->assertVisible($request, $article);

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404, 'Файл не найден.');

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name, [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Type'           => $attachment->mime_type,
        ]);
    }

    /**
     * Drafts and archived articles are 404 for the public (ТЗ 8.6). Staff see
     * them, so an article can be read through before it is published.
     */
    private function assertVisible(Request $request, KbArticle $article): void
    {
        if ($article->isPublished()) {
            return;
        }

        $user = $request->user();

        abort_unless($user && ($user->isAdmin() || $user->isSupport()), 404);
    }

    /**
     * ТЗ 8.6 counts reads, not refreshes. The guard is per session and the
     * list is capped, so a long browsing session cannot grow the cookie
     * payload without bound.
     */
    private function countView(Request $request, KbArticle $article): void
    {
        if (! $article->isPublished()) {
            return;
        }

        $seen = (array) $request->session()->get('kb_seen', []);

        if (in_array($article->id, $seen, true)) {
            return;
        }

        $article->recordView();

        $request->session()->put('kb_seen', array_slice([...$seen, $article->id], -self::VIEW_MEMORY));
    }
}
