@extends('layouts.public')

@section('title', 'Диплом № ' . $diploma->diploma_number . ' — Талант-центр')

@section('body-class', 'min-h-screen flex flex-col')

@section('head')
<style>
    .gold-shimmer {
        background: linear-gradient(
            90deg,
            #aa8345 0%,
            #D4AF37 30%,
            #f5d96b 50%,
            #D4AF37 70%,
            #aa8345 100%
        );
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: shimmer 3.5s linear infinite;
    }
    @keyframes shimmer {
        0%   { background-position: -200% center; }
        100% { background-position: 200% center; }
    }

    .diploma-card {
        background: linear-gradient(160deg, #FFFDF8 0%, #FAF8F2 60%, #F5F0E8 100%);
    }

    .corner-ornament {
        position: absolute;
        width: 28px;
        height: 28px;
        border-color: #D4AF37;
        border-style: solid;
        opacity: 0.5;
    }
    .corner-tl { top: 16px; left: 16px; border-width: 2px 0 0 2px; }
    .corner-tr { top: 16px; right: 16px; border-width: 2px 2px 0 0; }
    .corner-bl { bottom: 16px; left: 16px; border-width: 0 0 2px 2px; }
    .corner-br { bottom: 16px; right: 16px; border-width: 0 2px 2px 0; }

    .verify-badge {
        background: linear-gradient(135deg, #1a6b3a 0%, #22863a 100%);
        box-shadow: 0 4px 16px rgba(34, 134, 58, 0.35);
    }
</style>
@endsection

@section('content')

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-10 sm:py-14 px-4 text-center">

        {{-- Headline --}}
        <div class="mb-2">
            <span class="inline-block text-xs font-semibold tracking-[0.25em] text-warm-gray uppercase mb-2">Результат проверки</span>
            <h2 class="font-serif text-4xl md:text-5xl font-bold leading-tight">
                <span class="gold-shimmer">Диплом подлинный</span>
            </h2>
        </div>

        {{-- Diploma number badge --}}
        <div class="flex justify-center mt-4">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-gold/30 bg-white/70 backdrop-blur-sm text-sm font-mono text-dark shadow-sm">
                <i class="fas fa-hashtag text-gold text-xs"></i>
                {{ $diploma->diploma_number }}
            </span>
        </div>

        {{-- Verified by strip --}}
        <div class="flex justify-center mt-5">
            <div class="verify-badge inline-flex items-center gap-2 px-5 py-2 rounded-full text-white text-sm font-semibold shadow-md">
                <i class="fas fa-check-circle text-green-200 text-base"></i>
                Подтверждено платформой Талант-центр
            </div>
        </div>

    </section>

    <!-- ========== DIPLOMA CARD ========== -->
    <main class="flex-1 py-10 px-4">
        <div class="max-w-xl mx-auto">

            <div class="diploma-card rounded-2xl shadow-lg border border-gold/20 overflow-hidden relative">

                {{-- Corner ornaments --}}
                <div class="corner-ornament corner-tl"></div>
                <div class="corner-ornament corner-tr"></div>
                <div class="corner-ornament corner-bl"></div>
                <div class="corner-ornament corner-br"></div>

                {{-- Card header: award degree --}}
                <div class="px-8 pt-8 pb-6 text-center border-b border-gold/15" style="background: linear-gradient(180deg, rgba(212,175,55,0.06) 0%, transparent 100%);">
                    <p class="text-xs font-semibold tracking-[0.2em] text-warm-gray uppercase mb-2">Степень награды</p>
                    <p class="font-serif text-3xl font-bold text-dark leading-tight">
                        {{ $diploma->application?->status?->diplomaLabel() ?? '—' }}
                    </p>
                    <div class="w-16 h-0.5 mx-auto mt-4" style="background: linear-gradient(90deg, transparent, #D4AF37, transparent);"></div>
                </div>

                {{-- Data rows --}}
                <div class="px-8 py-6 space-y-0 divide-y divide-gold/10">

                    {{-- Participant --}}
                    <div class="card-row flex items-center gap-4 py-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 flex-shrink-0"
                             style="background: linear-gradient(135deg, #c8962a, #D4AF37);">
                            <i class="fas fa-user text-white text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold tracking-wider text-warm-gray uppercase mb-0.5">Участник</p>
                            <p class="font-serif font-bold text-dark text-lg leading-snug">{{ $diploma->user?->full_name ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Contest --}}
                    <div class="card-row flex items-center gap-4 py-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 flex-shrink-0 bg-primary/10">
                            <i class="fas fa-trophy text-primary text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold tracking-wider text-warm-gray uppercase mb-0.5">Конкурс</p>
                            <p class="font-semibold text-dark leading-snug">{{ $diploma->contest?->title ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Organisation --}}
                    <div class="card-row flex items-center gap-4 py-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 flex-shrink-0 bg-primary/10">
                            <i class="fas fa-building-columns text-primary text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold tracking-wider text-warm-gray uppercase mb-0.5">Организатор</p>
                            <p class="font-semibold text-dark leading-snug">{{ $diploma->contest?->organization?->name ?? '—' }}</p>
                        </div>
                    </div>

                    @if($diploma->application?->category)
                    <div class="card-row flex items-center gap-4 py-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 flex-shrink-0 bg-primary/10">
                            <i class="fas fa-bookmark text-primary text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold tracking-wider text-warm-gray uppercase mb-0.5">Номинация</p>
                            <p class="font-semibold text-dark leading-snug">{{ $diploma->application->category->name }}</p>
                        </div>
                    </div>
                    @endif

                    @if($diploma->application?->ageGroup)
                    <div class="card-row flex items-center gap-4 py-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 flex-shrink-0 bg-primary/10">
                            <i class="fas fa-layer-group text-primary text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold tracking-wider text-warm-gray uppercase mb-0.5">Возрастная категория</p>
                            <p class="font-semibold text-dark leading-snug">{{ $diploma->application->ageGroup->name }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Date --}}
                    <div class="card-row flex items-center gap-4 py-4">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 flex-shrink-0 bg-primary/10">
                            <i class="fas fa-calendar-check text-primary text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold tracking-wider text-warm-gray uppercase mb-0.5">Дата выдачи</p>
                            <p class="font-semibold text-dark">{{ $diploma->created_at?->format('d.m.Y') ?? '—' }}</p>
                        </div>
                    </div>

                </div>

                {{-- Card footer --}}
                <div class="px-8 py-5 border-t border-gold/15" style="background: linear-gradient(180deg, transparent 0%, rgba(212,175,55,0.04) 100%);">
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 mt-0.5 flex-shrink-0"
                             style="background: linear-gradient(135deg, #c8962a, #D4AF37);">
                            <i class="fas fa-check text-white" style="font-size: 9px;"></i>
                        </div>
                        <p class="text-xs text-warm-gray leading-relaxed">
                            Данный диплом является подлинным документом, выданным платформой Талант-центр.
                            Информация подтверждена системой на основе данных о конкурсе и участнике.
                        </p>
                    </div>
                </div>

            </div>

            {{-- Action buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <a href="{{ route('diplomvtrifi.search') }}"
                   class="flex-1 text-center border border-gold/40 text-dark font-semibold py-3 px-6 rounded-xl hover:bg-gold/5 transition-colors flex items-center justify-center gap-2 bg-white/60">
                    <i class="fas fa-search text-gold text-sm"></i>
                    Проверить другой диплом
                </a>
                <a href="{{ route('home') }}"
                   class="flex-1 text-center gradient-gold text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2 shadow-md">
                    <i class="fas fa-house text-sm"></i>
                    На главную
                </a>
            </div>

        </div>
    </main>

@endsection
