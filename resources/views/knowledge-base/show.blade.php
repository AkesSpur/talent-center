@extends('layouts.public')

@section('title', $article->title . ' — База знаний — Талант-центр')
@section('description', Str::limit($article->summary(300), 160))

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumbs (ТЗ 8.6) --}}
        <nav aria-label="Хлебные крошки" class="text-sm text-warm-gray mb-6">
            <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <li><a href="{{ route('knowledge-base.index') }}" class="hover:text-primary transition-colors">База знаний</a></li>
                <li aria-hidden="true">/</li>
                <li>
                    <a href="{{ route('knowledge-base.index') }}#category-{{ $article->category_id }}"
                        class="hover:text-primary transition-colors">{{ $article->category?->name }}</a>
                </li>
                @if($article->subcategory)
                    <li aria-hidden="true">/</li>
                    <li>{{ $article->subcategory->name }}</li>
                @endif
            </ol>
        </nav>

        @if($preview)
            <p class="mb-6 rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
                <i class="fas fa-eye-slash mr-2" aria-hidden="true"></i>
                Статья ещё не опубликована — её видите только вы как сотрудник. Просмотры не считаются.
            </p>
        @endif

        <article class="bg-white rounded-xl shadow-sm border border-gold/10 p-6 sm:p-8">
            <h1 class="font-serif text-2xl sm:text-3xl font-bold text-dark">{{ $article->title }}</h1>

            <p class="text-xs text-warm-gray mt-2">
                @if($article->published_at)
                    Опубликовано {{ $article->published_at->format('d.m.Y') }}
                @endif
                @if($article->updated_at && $article->published_at && $article->updated_at->gt($article->published_at))
                    · обновлено {{ $article->updated_at->format('d.m.Y') }}
                @endif
            </p>

            {{-- Sanitised on save as well; a template that forgets one is then
                 still not an XSS hole. --}}
            <div class="rich-content text-dark leading-relaxed mt-6">{!! clean($article->content, 'kb') !!}</div>

            @if($article->attachments->isNotEmpty())
                <div class="mt-8 pt-6 border-t border-gold/10">
                    <h2 class="font-serif text-lg font-semibold text-dark mb-3">Вложения</h2>
                    <ul class="space-y-2">
                        @foreach($article->attachments as $attachment)
                            <li>
                                <a href="{{ route('knowledge-base.attachment', $attachment->token) }}"
                                    class="flex items-center gap-3 p-3 rounded-lg border border-gold/10 bg-cream hover:border-primary/30 transition-colors">
                                    <i class="fas {{ $attachment->iconClass() }} text-primary/60 w-5 text-center" aria-hidden="true"></i>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm text-dark truncate">{{ $attachment->original_name }}</span>
                                        <span class="block text-xs text-warm-gray">{{ $attachment->humanSize() }}</span>
                                    </span>
                                    <i class="fas fa-download text-warm-gray" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </article>

        {{-- ТЗ 8.6: the article's own category is pre-selected on the form. --}}
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gold/10 p-6 text-center">
            <p class="text-warm-gray">Статья не ответила на ваш вопрос?</p>
            <a href="{{ route('tickets.create', ['category' => $article->category_id]) }}"
                class="inline-flex items-center mt-3 px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                <i class="fas fa-headset mr-2" aria-hidden="true"></i>Создать заявку
            </a>
        </div>

        <div class="mt-6">
            <a href="{{ route('knowledge-base.index') }}" class="text-sm text-primary hover:underline">
                <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>Все статьи
            </a>
        </div>
    </div>

@endsection
