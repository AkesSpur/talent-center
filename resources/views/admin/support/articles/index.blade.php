<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <x-breadcrumbs :items="[
                    ['label' => 'Поддержка', 'url' => route('admin.support.tickets.index')],
                    ['label' => 'База знаний'],
                ]" />
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">База знаний</h2>
                <p class="text-warm-gray mt-1">Статьи с ответами на частые вопросы</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('knowledge-base.index') }}" target="_blank" rel="noopener"
                    class="self-start inline-flex items-center px-4 py-2.5 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm">
                    <i class="fas fa-arrow-up-right-from-square mr-2"></i>Как видят пользователи
                </a>
                <a href="{{ route('admin.support.articles.create') }}"
                    class="self-start inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                    <i class="fas fa-plus mr-2"></i>Новая статья
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.support.articles.index') }}"
                class="bg-white rounded-xl shadow-sm border border-gold/10 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-2">
                    <label for="f-search" class="sr-only">Поиск</label>
                    <input id="f-search" name="search" type="search" value="{{ request('search') }}"
                        placeholder="Поиск по заголовку, описанию, тексту и категории"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                </div>
                <div>
                    <label for="f-category" class="sr-only">Категория</label>
                    <select id="f-category" name="category"
                        class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <option value="">Все категории</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                            @foreach($category->children as $child)
                                <option value="{{ $child->id }}" @selected(request('category') == $child->id)>— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <label for="f-status" class="sr-only">Статус</label>
                    <select id="f-status" name="status"
                        class="flex-1 px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <option value="">Все статусы</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="px-4 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm shrink-0">
                        <i class="fas fa-search" aria-hidden="true"></i><span class="sr-only">Найти</span>
                    </button>
                </div>
                @if(request()->hasAny(['search', 'category', 'status']))
                    <div class="sm:col-span-2 lg:col-span-4">
                        <a href="{{ route('admin.support.articles.index') }}" class="text-sm text-primary hover:underline">Сбросить фильтры</a>
                    </div>
                @endif
            </form>

            @if($articles->isEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-10 text-center">
                    <i class="fas fa-book-open text-3xl text-primary/30 mb-3" aria-hidden="true"></i>
                    <p class="text-warm-gray">
                        @if(request()->hasAny(['search', 'category', 'status']))
                            По этим условиям статей не нашлось.
                        @else
                            Статей пока нет. Первая появится здесь после сохранения.
                        @endif
                    </p>
                </div>
            @else
                {{-- Phones: cards, as on the ticket queue. --}}
                <div class="space-y-3 md:hidden">
                    @foreach($articles as $article)
                        <a href="{{ route('admin.support.articles.edit', $article) }}"
                            class="block bg-white rounded-xl shadow-sm border border-gold/10 p-4 active:bg-cream/40 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-medium text-dark">{{ Str::limit($article->title, 70) }}</p>
                                <span class="shrink-0 px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap {{ $article->status->color() }}">
                                    {{ $article->status->label() }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-warm-gray">
                                {{ $article->category?->name ?? '—' }}@if($article->subcategory) · {{ $article->subcategory->name }}@endif
                            </p>
                            <p class="mt-1 text-xs text-warm-gray">
                                {{ $article->updated_at?->format('d.m.Y') ?? '—' }} ·
                                <i class="fas fa-eye" aria-hidden="true"></i> {{ $article->views_count }}
                            </p>
                        </a>
                    @endforeach
                </div>

                <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-4 py-3 font-semibold">Заголовок</th>
                                    <th class="text-left px-4 py-3 font-semibold">Категория</th>
                                    <th class="text-left px-4 py-3 font-semibold w-32">Статус</th>
                                    <th class="text-left px-4 py-3 font-semibold w-28 hidden lg:table-cell">Обновлена</th>
                                    <th class="text-right px-4 py-3 font-semibold w-24">Просмотров</th>
                                    <th class="text-right px-4 py-3 font-semibold w-28">Порядок</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold/10">
                                @foreach($articles as $article)
                                    <tr class="hover:bg-cream/40 transition-colors">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.support.articles.edit', $article) }}"
                                                class="font-medium text-dark hover:text-primary transition-colors">
                                                {{ Str::limit($article->title, 80) }}
                                            </a>
                                            @if($article->excerpt)
                                                <p class="text-xs text-warm-gray mt-0.5">{{ Str::limit($article->excerpt, 90) }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-warm-gray">
                                            {{ $article->category?->name ?? '—' }}
                                            @if($article->subcategory)
                                                <span class="block text-xs">{{ $article->subcategory->name }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap {{ $article->status->color() }}">
                                                {{ $article->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-warm-gray whitespace-nowrap hidden lg:table-cell">
                                            {{ $article->updated_at?->format('d.m.Y') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-warm-gray">{{ $article->views_count }}</td>
                                        <td class="px-4 py-3 text-right text-warm-gray">{{ $article->sort_order }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>{{ $articles->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
