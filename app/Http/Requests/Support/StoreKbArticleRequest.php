<?php

declare(strict_types=1);

namespace App\Http\Requests\Support;

use App\Enums\KbArticleStatus;
use App\Models\KbArticle;
use App\Models\SupportCategory;
use App\Services\SupportAttachmentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Creating and editing a knowledge-base article (ТЗ 8.5). Serves both, because
 * the only rule that differs is which article the slug may collide with.
 */
class StoreKbArticleRequest extends FormRequest
{
    private const PROFILE = 'article';

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function article(): ?KbArticle
    {
        $article = $this->route('article');

        return $article instanceof KbArticle ? $article : null;
    }

    protected function prepareForValidation(): void
    {
        $content = (string) $this->input('content', '');

        // Quill posts «<p><br></p>» for an untouched editor. An article made
        // only of a picture is still an article, so images count as content.
        if (KbArticle::plainText($content) === '' && ! str_contains($content, '<img')) {
            $this->merge(['content' => '']);
        }

        if ($this->input('sort_order') === '') {
            $this->merge(['sort_order' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:200'],
            'slug'           => [
                'nullable', 'string', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('kb_articles', 'slug')->ignore($this->article()?->id),
            ],
            'category_id'    => ['required', 'integer', 'exists:support_categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:support_categories,id'],
            'excerpt'        => ['nullable', 'string', 'max:300'],
            'content'        => ['required', 'string', 'max:200000'],
            'status'         => ['required', Rule::enum(KbArticleStatus::class)],
            'sort_order'     => ['nullable', 'integer', 'min:0', 'max:65535'],
            'files'          => ['nullable', 'array'],
            'files.*'        => StoreSupportTicketRequest::fileRules(),
            'remove'         => ['nullable', 'array'],
            'remove.*'       => ['string', 'max:64'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->checkCategories($validator);
            $this->checkAttachmentBudget($validator);
        });
    }

    /**
     * The category must be top-level, and the subcategory must belong to it —
     * otherwise a stale dependent select would file the article under a pair
     * that the public tree can never show together.
     */
    private function checkCategories(Validator $validator): void
    {
        $category = SupportCategory::find($this->input('category_id'));

        if ($category === null) {
            return;
        }

        if ($category->isSubcategory()) {
            $validator->errors()->add('category_id', 'Выберите категорию верхнего уровня.');

            return;
        }

        $subcategoryId = $this->input('subcategory_id');

        if (! $subcategoryId) {
            return;
        }

        $subcategory = SupportCategory::find($subcategoryId);

        if (! $subcategory || $subcategory->parent_id !== $category->id) {
            $validator->errors()->add('subcategory_id', 'Подкатегория не относится к выбранной категории.');
        }
    }

    /**
     * ТЗ 8.9 counts files per article, not per request: an edit that adds two
     * files to eight existing ones is over the limit even though it uploaded
     * only two. Files marked for removal free their slot in the same save.
     */
    private function checkAttachmentBudget(Validator $validator): void
    {
        $kept = $this->keptAttachments();

        $newFiles = collect($this->file('files') ?? []);
        $count = $kept->count() + $newFiles->count();
        $bytes = (int) $kept->sum('size_bytes') + (int) $newFiles->sum(fn ($file) => $file->getSize());

        $maxFiles = SupportAttachmentService::maxFiles(self::PROFILE);
        $maxBytes = SupportAttachmentService::KB_MAX_TOTAL_KB * 1024;

        if ($count > $maxFiles) {
            $validator->errors()->add('files', "К статье можно прикрепить не более {$maxFiles} файлов.");
        }

        if ($bytes > $maxBytes) {
            $validator->errors()->add('files', 'Суммарный размер вложений статьи не должен превышать '
                . SupportAttachmentService::formatBytes($maxBytes) . '.');
        }
    }

    /** Attachments the article will still have after this save. */
    public function keptAttachments(): Collection
    {
        $article = $this->article();

        if ($article === null) {
            return collect();
        }

        $removing = array_map('strval', (array) $this->input('remove', []));

        return $article->attachments
            ->reject(fn ($attachment) => in_array((string) $attachment->token, $removing, true))
            ->values();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return StoreSupportTicketRequest::fileAttributes($this);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required'          => 'Укажите заголовок статьи.',
            'title.max'               => 'Заголовок не должен превышать 200 символов.',
            'slug.regex'              => 'Адрес может содержать только латинские буквы, цифры и дефис.',
            'slug.unique'             => 'Статья с таким адресом уже есть — измените адрес.',
            'slug.max'                => 'Адрес не должен превышать 200 символов.',
            'category_id.required'    => 'Выберите категорию.',
            'category_id.exists'      => 'Выберите категорию из списка.',
            'subcategory_id.exists'   => 'Выберите подкатегорию из списка.',
            'excerpt.max'             => 'Краткое описание не должно превышать 300 символов.',
            'content.required'        => 'Напишите текст статьи.',
            'content.max'             => 'Текст статьи слишком длинный.',
            'status.required'         => 'Выберите статус статьи.',
            'sort_order.integer'      => 'Порядок — целое число.',
            'sort_order.min'          => 'Порядок не может быть отрицательным.',
        ] + StoreSupportTicketRequest::fileMessages();
    }
}
