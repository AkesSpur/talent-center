@php
    /** @var \App\Models\KbArticle|null $article */
    $article ??= null;
    $tree = $categories->mapWithKeys(fn ($category) => [
        (string) $category->id => $category->children->map(fn ($child) => [
            'id'   => $child->id,
            'name' => $child->name,
        ])->values(),
    ]);
@endphp

<x-support.upload-form :action="$action" profile="article"
    class="bg-white rounded-xl shadow-sm border border-gold/10 p-6 sm:p-8">

    @if($article)
        @method('PUT')
    @endif

    {{-- Inside the form, not around it: x-ref belongs to the nearest x-data
         root, and the form is one. From here the editor's own refs resolve,
         and the upload component's `sending` is still in scope. --}}
    <div class="space-y-6" x-data="kbEditor({
            content: @js(old('content', $article?->content ?? '')),
            imageUrl: @js(route('admin.support.articles.image')),
            tree: @js($tree),
            category: @js((string) old('category_id', $article?->category_id ?? '')),
            subcategory: @js((string) old('subcategory_id', $article?->subcategory_id ?? '')),
        })">

        {{-- Title --}}
        <div x-data="{ title: @js(old('title', $article?->title ?? '')) }">
            <label for="title" class="block text-sm font-medium text-dark mb-2">
                Заголовок <span class="text-red-500">*</span>
            </label>
            <input id="title" name="title" type="text" required maxlength="200" x-model="title"
                value="{{ old('title', $article?->title) }}"
                placeholder="Например: Как оплатить участие в конкурсе"
                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
            <div class="flex justify-between gap-3 mt-1.5">
                <x-support.field-error field="title" />
                <span class="text-xs text-warm-gray ml-auto shrink-0" x-text="title.length + ' / 200'"></span>
            </div>
        </div>

        {{-- Category / subcategory --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category_id" class="block text-sm font-medium text-dark mb-2">
                    Категория <span class="text-red-500">*</span>
                </label>
                <select id="category_id" name="category_id" required x-model="category"
                    class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                    <option value="">Выберите категорию</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            @selected((string) old('category_id', $article?->category_id) === (string) $category->id)>
                            {{ $category->name }}@unless($category->is_active) (в архиве)@endunless
                        </option>
                    @endforeach
                </select>
                <x-support.field-error field="category_id" class="mt-2" />
            </div>

            <div>
                <label for="subcategory_id" class="block text-sm font-medium text-dark mb-2">Подкатегория</label>
                <select id="subcategory_id" name="subcategory_id" x-model="subcategory"
                    :disabled="!subcategories.length"
                    class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm disabled:bg-cream disabled:text-warm-gray">
                    <option value="">Без подкатегории</option>
                    <template x-for="item in subcategories" :key="item.id">
                        <option :value="item.id" x-text="item.name"></option>
                    </template>
                </select>
                <x-support.field-error field="subcategory_id" class="mt-2" />
            </div>
        </div>

        {{-- Excerpt --}}
        <div x-data="{ excerpt: @js(old('excerpt', $article?->excerpt ?? '')) }">
            <label for="excerpt" class="block text-sm font-medium text-dark mb-2">Краткое описание</label>
            <textarea id="excerpt" name="excerpt" rows="2" maxlength="300" x-model="excerpt"
                placeholder="Одно-два предложения — показываются в списке статей и в результатах поиска"
                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm resize-y">{{ old('excerpt', $article?->excerpt) }}</textarea>
            <div class="flex justify-between gap-3 mt-1.5">
                <x-support.field-error field="excerpt" />
                <span class="text-xs text-warm-gray ml-auto shrink-0" x-text="excerpt.length + ' / 300'"></span>
            </div>
        </div>

        {{-- Body --}}
        <div>
            <label for="kb-body" class="block text-sm font-medium text-dark mb-2">
                Текст статьи <span class="text-red-500">*</span>
            </label>

            <div id="kb-body" x-ref="editor" class="kb-editor"></div>

            {{-- What actually gets posted; revealed only if Quill fails to load. --}}
            <textarea x-ref="input" name="content" rows="12"
                class="hidden w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm font-mono resize-y">{{ old('content', $article?->content) }}</textarea>
            <p x-ref="fallback" class="hidden mt-2 text-sm text-red-600">
                Визуальный редактор не загрузился. Текст можно ввести как HTML — форматирование сохранится.
            </p>

            <p class="mt-2 text-xs text-warm-gray">
                Изображения загружаются кнопкой с картинкой на панели. Вставленные из буфера — тоже,
                но скопированные вместе с текстом с другого сайта не сохранятся.
            </p>

            <input x-ref="imageInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                multiple class="hidden" @change="imagePicked($event)">

            <p x-show="uploading" x-cloak class="mt-2 text-xs text-primary">
                <i class="fas fa-circle-notch fa-spin mr-1" aria-hidden="true"></i>Загрузка изображения…
            </p>
            <p x-show="imageError" x-cloak role="alert" class="mt-2 text-sm text-red-600" x-text="imageError"></p>

            <x-support.field-error field="content" class="mt-2" />
        </div>

        {{-- Existing attachments --}}
        @if($article && $article->attachments->isNotEmpty())
            <div>
                <p class="block text-sm font-medium text-dark mb-2">Вложения статьи</p>
                <ul class="space-y-2">
                    @foreach($article->attachments as $attachment)
                        <li class="flex items-center gap-3 p-2 pr-3 rounded-lg border border-gold/10 bg-cream">
                            <i class="fas {{ $attachment->iconClass() }} text-primary/60 w-5 text-center" aria-hidden="true"></i>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm text-dark truncate">{{ $attachment->original_name }}</span>
                                <span class="block text-xs text-warm-gray">{{ $attachment->humanSize() }}</span>
                            </span>
                            <label class="inline-flex items-center gap-2 text-xs text-warm-gray cursor-pointer shrink-0">
                                <input type="checkbox" name="remove[]" value="{{ $attachment->token }}"
                                    class="rounded border-primary/30 text-red-600">
                                Удалить
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- New attachments --}}
        <div>
            <p class="block text-sm font-medium text-dark mb-2">Добавить вложения</p>
            <x-support.file-picker id="article-files" profile="article" compact />
        </div>

        {{-- Status / order --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gold/10">
            <div>
                <label for="status" class="block text-sm font-medium text-dark mb-2">
                    Статус <span class="text-red-500">*</span>
                </label>
                <select id="status" name="status" required
                    class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}"
                            @selected(old('status', $article?->status?->value ?? 'draft') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <x-support.field-error field="status" class="mt-2" />
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-dark mb-2">Порядок</label>
                <input id="sort_order" name="sort_order" type="number" min="0" max="65535"
                    value="{{ old('sort_order', $article?->sort_order ?? 0) }}"
                    class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm no-spinner">
                <p class="text-xs text-warm-gray mt-1.5">Меньше — выше в списке категории.</p>
                <x-support.field-error field="sort_order" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <label for="slug" class="block text-sm font-medium text-dark mb-2">Адрес страницы</label>
                <input id="slug" name="slug" type="text" maxlength="200"
                    value="{{ old('slug', $article?->slug) }}"
                    placeholder="{{ $article?->slug ?? 'формируется из заголовка' }}"
                    class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm font-mono">
                <p class="text-xs text-warm-gray mt-1.5">
                    {{ url('/knowledge-base') }}/<span class="text-dark">{{ $article?->slug ?? '…' }}</span>
                    @if($article?->published_at)
                        · после публикации адрес не меняется сам — ссылки в старых ответах должны работать.
                    @endif
                </p>
                <x-support.field-error field="slug" class="mt-2" />
            </div>
        </div>

        <x-support.upload-status />

        <div class="flex flex-wrap gap-3 pt-2 border-t border-gold/10">
            <button type="submit" :disabled="sending || uploading > 0"
                class="px-6 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm disabled:opacity-60 disabled:cursor-wait">
                <span x-show="!sending"><i class="fas fa-floppy-disk mr-2" aria-hidden="true"></i>Сохранить</span>
                <span x-show="sending" x-cloak><i class="fas fa-circle-notch fa-spin mr-2" aria-hidden="true"></i>Сохранение…</span>
            </button>
            @if($article?->isPublished())
                <a href="{{ $article->publicUrl() }}" target="_blank" rel="noopener"
                    class="px-6 py-2.5 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 transition-colors text-sm">
                    <i class="fas fa-arrow-up-right-from-square mr-2" aria-hidden="true"></i>Открыть на сайте
                </a>
            @endif
            <a href="{{ route('admin.support.articles.index') }}"
                class="px-6 py-2.5 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 transition-colors text-sm">
                Отмена
            </a>
        </div>
    </div>
</x-support.upload-form>

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow { border: 1px solid rgba(139,69,19,.2); border-bottom: none; border-radius: .5rem .5rem 0 0; background: #FAF8F5; }
    .ql-container.ql-snow { border: 1px solid rgba(139,69,19,.2); border-radius: 0 0 .5rem .5rem; font-family: 'Inter', sans-serif; font-size: .875rem; }
    .ql-editor { min-height: 340px; color: #2C2416; }
    .ql-editor img { max-width: 100%; height: auto; }
    .ql-editor.ql-blank::before { color: #9A8B7A; font-style: normal; }
    .ql-toolbar.ql-snow .ql-formats button:hover .ql-stroke, .ql-toolbar.ql-snow .ql-formats button.ql-active .ql-stroke { stroke: #8B4513; }
    .ql-toolbar.ql-snow .ql-formats button:hover .ql-fill, .ql-toolbar.ql-snow .ql-formats button.ql-active .ql-fill { fill: #8B4513; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
@endpush
