@extends('layouts.public')

@section('title', 'Поиск по базе знаний — Талант-центр')

@section('content')

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <nav aria-label="Хлебные крошки" class="text-sm text-warm-gray mb-6">
            <a href="{{ route('knowledge-base.index') }}" class="hover:text-primary transition-colors">База знаний</a>
            <span aria-hidden="true" class="mx-2">/</span>
            <span>Поиск</span>
        </nav>

        <form method="GET" action="{{ route('knowledge-base.search') }}" class="flex gap-2">
            <label for="kb-q" class="sr-only">Поиск по базе знаний</label>
            <input id="kb-q" name="q" type="search" value="{{ $term }}" placeholder="О чём вопрос?"
                class="flex-1 min-w-0 px-4 py-3 border border-primary/20 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
            <button type="submit"
                class="shrink-0 px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                <i class="fas fa-search sm:mr-2" aria-hidden="true"></i><span class="hidden sm:inline">Найти</span>
            </button>
        </form>

        @if($term === '')
            <p class="mt-8 text-warm-gray">Введите запрос — поиск идёт по заголовкам и тексту статей.</p>
        @elseif($results->isEmpty())
            <div class="mt-8 bg-white rounded-xl shadow-sm border border-gold/10 p-8 text-center">
                <p class="text-dark font-medium">По запросу «{{ $term }}» ничего не нашлось.</p>
                <p class="text-warm-gray text-sm mt-2">Попробуйте другое слово — или напишите в поддержку.</p>
                <a href="{{ route('tickets.create') }}"
                    class="inline-flex items-center mt-5 px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                    <i class="fas fa-headset mr-2" aria-hidden="true"></i>Создать заявку
                </a>
            </div>
        @else
            {{-- Count first, noun fixed: works for every number without pluralisation. --}}
            <p class="mt-6 text-sm text-warm-gray">Найдено статей: {{ $results->count() }}</p>

            <ul class="mt-4 space-y-3">
                @foreach($results as $article)
                    <li>
                        <a href="{{ route('knowledge-base.show', $article->slug) }}"
                            class="block bg-white rounded-xl shadow-sm border border-gold/10 p-5 hover:border-primary/30 transition-colors">
                            <p class="font-medium text-dark">{{ $article->title }}</p>
                            @if($summary = $article->summary())
                                <p class="text-sm text-warm-gray mt-1">{{ $summary }}</p>
                            @endif
                            <p class="text-xs text-warm-gray mt-2">{{ $article->category?->name }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

@endsection
