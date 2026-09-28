<x-app-layout>
    <x-slot name="header">
        <x-breadcrumbs :items="[
            ['label' => 'Поддержка', 'url' => route('admin.support.tickets.index')],
            ['label' => 'База знаний', 'url' => route('admin.support.articles.index')],
            ['label' => Str::limit($article->title, 60)],
        ]" />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">{{ $article->title }}</h2>
                <p class="text-warm-gray mt-1">Редактирование статьи</p>
            </div>
            <span class="self-start px-3 py-1 rounded-full text-xs font-medium {{ $article->status->color() }}">
                {{ $article->status->label() }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-notify />

            {{-- Stats and transitions (ТЗ 8.4, 8.5) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-warm-gray">Просмотров</dt>
                        <dd class="text-dark font-semibold mt-0.5">{{ $article->views_count }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-warm-gray">Создана</dt>
                        <dd class="text-dark mt-0.5">{{ $article->created_at?->format('d.m.Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-warm-gray">Обновлена</dt>
                        <dd class="text-dark mt-0.5">{{ $article->updated_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-warm-gray">Опубликована</dt>
                        <dd class="text-dark mt-0.5">{{ $article->published_at?->format('d.m.Y') ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs text-warm-gray">Автор</dt>
                        <dd class="text-dark mt-0.5">{{ $article->author?->full_name ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs text-warm-gray">Последним редактировал</dt>
                        <dd class="text-dark mt-0.5">{{ $article->updatedBy?->full_name ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="flex flex-wrap gap-2 mt-5 pt-5 border-t border-gold/10">
                    @foreach($article->status->transitions() as $target => $label)
                        <form method="POST" action="{{ route('admin.support.articles.status', $article) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $target }}">
                            <button type="submit"
                                class="px-4 py-2 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm">
                                {{ $label }}
                            </button>
                        </form>
                    @endforeach
                    <a href="{{ route('admin.action-logs.index', ['target_type' => 'kb_article']) }}"
                        class="px-4 py-2 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 transition-colors text-sm">
                        <i class="fas fa-list-check mr-2" aria-hidden="true"></i>Журнал действий
                    </a>
                </div>
            </div>

            @include('admin.support.articles._form', [
                'action'  => route('admin.support.articles.update', $article),
                'article' => $article,
            ])
        </div>
    </div>
</x-app-layout>
