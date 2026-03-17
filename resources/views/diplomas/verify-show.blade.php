@extends('layouts.public')

@section('title', 'Диплом № ' . $diploma->diploma_number . ' — Талант-центр')

@section('body-class', 'min-h-screen flex flex-col')

@section('content')

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-8 sm:py-10 px-4 text-center">
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 gradient-gold rounded-full flex items-center justify-center shadow-md">
                <i class="fas fa-shield-check text-white text-2xl"></i>
            </div>
        </div>
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-dark mb-2">
            Диплом подлинный
        </h2>
        <p class="text-warm-gray text-md">
            Выдан платформой Талант-центр · № {{ $diploma->diploma_number }}
        </p>
    </section>

    <!-- ========== MAIN ========== -->
    <main class="flex-1 py-12 px-4">
        <div class="max-w-2xl mx-auto">

            <!-- Diploma card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gold/10 overflow-hidden">

                <!-- Card header -->
                <div class="pattern-bg px-8 py-6 border-b border-gold/20">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs text-warm-gray uppercase tracking-widest mb-1">Степень награды</p>
                            <h1 class="font-serif text-2xl font-bold text-dark leading-tight">
                                {{ $diploma->application?->status?->diplomaLabel() ?? '—' }}
                            </h1>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-block bg-gold/10 text-dark text-xs font-mono px-3 py-1.5 rounded-full border border-gold/30">
                                <i class="fas fa-hashtag text-gold mr-1"></i>{{ $diploma->diploma_number }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card body -->
                <div class="px-8 py-6 space-y-5">

                    <!-- Participant -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full gradient-gold flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-warm-gray uppercase tracking-wide mb-0.5">Участник</p>
                            <p class="font-serif font-bold text-dark text-lg">{{ $diploma->user?->full_name ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <!-- Contest -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-trophy text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-warm-gray uppercase tracking-wide mb-0.5">Конкурс</p>
                            <p class="font-semibold text-dark">{{ $diploma->contest?->title ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <!-- Organisation -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-building-columns text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-warm-gray uppercase tracking-wide mb-0.5">Организатор</p>
                            <p class="font-semibold text-dark">{{ $diploma->contest?->organization?->name ?? '—' }}</p>
                        </div>
                    </div>

                    @if($diploma->application?->category)
                    <div class="border-t border-gray-100"></div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-bookmark text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-warm-gray uppercase tracking-wide mb-0.5">Номинация</p>
                            <p class="font-semibold text-dark">{{ $diploma->application->category->name }}</p>
                        </div>
                    </div>
                    @endif

                    @if($diploma->application?->ageGroup)
                    <div class="border-t border-gray-100"></div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-layer-group text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-warm-gray uppercase tracking-wide mb-0.5">Возрастная категория</p>
                            <p class="font-semibold text-dark">{{ $diploma->application->ageGroup->name }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="border-t border-gray-100"></div>

                    <!-- Date -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-calendar-check text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-warm-gray uppercase tracking-wide mb-0.5">Дата выдачи</p>
                            <p class="font-semibold text-dark">
                                {{ $diploma->created_at?->format('d.m.Y') ?? '—' }}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Card footer -->
                <div class="px-8 py-5 bg-gold/5 border-t border-gold/20 rounded-b-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 gradient-gold rounded-full flex items-center justify-center shrink-0">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                        <p class="text-xs text-warm-gray leading-relaxed">
                            Данный диплом является подлинным документом, выданным платформой Талант-центр.
                            Информация подтверждена системой на основе данных о конкурсе и участнике.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <a
                    href="{{ route('diplomvtrifi.search') }}"
                    class="flex-1 text-center border border-gold/40 text-dark font-semibold py-3 px-6 rounded-xl hover:bg-gold/5 transition-colors flex items-center justify-center gap-2"
                >
                    <i class="fas fa-search text-gold"></i>
                    Проверить другой диплом
                </a>
                <a
                    href="{{ route('home') }}"
                    class="flex-1 text-center gradient-gold text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                >
                    <i class="fas fa-house"></i>
                    На главную
                </a>
            </div>

        </div>
    </main>

@endsection
