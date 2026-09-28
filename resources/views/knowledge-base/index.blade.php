@extends('layouts.public')

@section('title', 'База знаний — Талант-центр')
@section('description', 'Ответы на частые вопросы о конкурсах, заявках, оплате и дипломах.')

@section('content')

    <div class="pattern-bg py-10 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-dark">База знаний</h1>
            <p class="text-warm-gray mt-2">Ответы на частые вопросы — возможно, ваш уже здесь</p>

            <form method="GET" action="{{ route('knowledge-base.search') }}" class="mt-6 flex gap-2 max-w-xl mx-auto">
                <label for="kb-q" class="sr-only">Поиск по базе знаний</label>
                <input id="kb-q" name="q" type="search" placeholder="О чём вопрос?"
                    class="flex-1 min-w-0 px-4 py-3 border border-primary/20 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                <button type="submit"
                    class="shrink-0 px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                    <i class="fas fa-search sm:mr-2" aria-hidden="true"></i><span class="hidden sm:inline">Найти</span>
                </button>
            </form>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if($categories->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-10 text-center">
                <i class="fas fa-book-open text-3xl text-primary/30 mb-3" aria-hidden="true"></i>
                <p class="text-warm-gray">Статей пока нет. Если у вас есть вопрос — напишите в поддержку.</p>
                <a href="{{ route('tickets.create') }}"
                    class="inline-flex items-center mt-5 px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                    <i class="fas fa-headset mr-2" aria-hidden="true"></i>Создать заявку
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($categories as $category)
                    <section class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        <h2 class="font-serif text-xl font-bold text-dark">{{ $category->name }}</h2>
                        @if($category->description)
                            <p class="text-sm text-warm-gray mt-1">{{ $category->description }}</p>
                        @endif

                        <ul class="mt-4 divide-y divide-gold/10">
                            @foreach($articles[$category->id] as $article)
                                <li>
                                    <a href="{{ route('knowledge-base.show', $article->slug) }}"
                                        class="block py-3 group">
                                        <span class="font-medium text-dark group-hover:text-primary transition-colors">
                                            {{ $article->title }}
                                        </span>
                                        @if($summary = $article->summary())
                                            <span class="block text-sm text-warm-gray mt-0.5">{{ $summary }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            <div class="mt-8 bg-white rounded-xl shadow-sm border border-gold/10 p-6 text-center">
                <p class="text-warm-gray">Не нашли ответ?</p>
                <a href="{{ route('tickets.create') }}"
                    class="inline-flex items-center mt-3 px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                    <i class="fas fa-headset mr-2" aria-hidden="true"></i>Создать заявку
                </a>
            </div>
        @endif
    </div>

@endsection
