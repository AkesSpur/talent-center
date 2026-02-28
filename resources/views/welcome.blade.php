<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Талант-центр — Всероссийская платформа конкурсов</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .hero-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(44, 36, 22, 0.12);
        }
        .gold-line {
            width: 48px;
            height: 3px;
            background: linear-gradient(90deg, #D4AF37, #F4D03F);
            border-radius: 2px;
        }
        .step-number {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.02em;
        }
        .contest-card-img {
            aspect-ratio: 16/9;
            object-fit: cover;
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(-2deg); }
            50% { transform: translateY(-8px) rotate(-2deg); }
        }
        @keyframes float-med {
            0%, 100% { transform: translateY(0px) rotate(3deg); }
            50% { transform: translateY(-6px) rotate(3deg); }
        }
        .float-card-1 { animation: float-slow 6s ease-in-out infinite; }
        .float-card-2 { animation: float-med 5s ease-in-out infinite 0.8s; }
        .float-card-3 { animation: float-slow 7s ease-in-out infinite 1.6s; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="font-sans antialiased bg-cream text-dark">

{{-- ===== HEADER ===== --}}
<header class="bg-cream shadow-sm sticky top-0 z-50" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-4">
            <a href="/" class="flex items-center space-x-3 shrink-0">
                <div class="w-11 h-11 gradient-gold rounded-full flex items-center justify-center shadow-sm">
                    <i class="fas fa-award text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="font-serif text-lg font-bold text-primary leading-tight">Талант-центр</h1>
                    <p class="text-xs text-warm-gray leading-tight">Всероссийский центр талантов</p>
                </div>
            </a>

            <nav class="hidden lg:flex items-center justify-center gap-8">
                <a href="/" class="text-sm font-medium text-primary border-b-2 border-primary pb-0.5">Главная</a>
                <a href="{{ route('contests.index') }}" class="text-sm font-medium text-warm-gray hover:text-primary transition-colors">Конкурсы</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="inline-flex items-center space-x-2 px-3 py-2 text-sm font-medium text-dark hover:text-primary transition">
                            <x-user-avatar :user="Auth::user()" size="sm" />
                            <span class="hidden sm:inline">{{ Auth::user()->last_name }} {{ mb_substr(Auth::user()->first_name, 0, 1) }}.</span>
                            <i class="fas fa-chevron-down text-xs text-warm-gray"></i>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 z-50 mt-2 w-64 rounded-xl shadow-lg bg-white ring-1 ring-gold/20 py-1"
                             style="display:none;">
                            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-sm text-dark hover:bg-cream transition">
                                <i class="fas fa-th-large mr-2 text-warm-gray w-4"></i> Личный кабинет
                            </a>
                            <a href="{{ route('dashboard.applications') }}" class="flex items-center px-4 py-2 text-sm text-dark hover:bg-cream transition">
                                <i class="fas fa-file-alt mr-2 text-warm-gray w-4"></i> Мои заявки
                            </a>
                            <a href="{{ route('contests.index') }}" class="flex items-center px-4 py-2 text-sm text-dark hover:bg-cream transition">
                                <i class="fas fa-trophy mr-2 text-warm-gray w-4"></i> Конкурсы
                            </a>
                            @if(auth()->user()->isAdmin())
                                <div class="border-t border-gold/10 mt-1 pt-1">
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-dark hover:bg-cream transition">
                                        <i class="fas fa-cog mr-2 text-warm-gray w-4"></i> Админ-панель
                                    </a>
                                </div>
                            @endif
                            @if(auth()->user()->isSupport())
                                <div class="border-t border-gold/10 mt-1 pt-1">
                                    <a href="{{ route('support.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-dark hover:bg-cream transition">
                                        <i class="fas fa-headset mr-2 text-warm-gray w-4"></i> Панель поддержки
                                    </a>
                                </div>
                            @endif
                            <div class="border-t border-gold/10 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt mr-2 w-4"></i> Выйти
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-warm-gray hover:text-primary transition">Войти</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-block px-5 py-2 gradient-gold text-dark font-semibold rounded-lg text-sm hover:opacity-90 transition">
                        Регистрация
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

{{-- ===== HERO ===== --}}
<section class="relative overflow-hidden pattern-bg" style="background-color: #FAF8F5; min-height: 88vh;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid lg:grid-cols-5 gap-6 lg:gap-4 items-center">

            {{-- Left: Text --}}
            <div class="lg:col-span-3">
                <div class="inline-flex items-center gap-2 mb-6">
                    <div class="w-6 h-px bg-gold"></div>
                    <span class="text-xs font-semibold text-gold uppercase tracking-[0.2em]">Всероссийский центр талантов</span>
                </div>

                <h2 class="font-serif text-5xl sm:text-6xl lg:text-7xl font-bold text-dark leading-[1.05] mb-6">
                    Проводи конкурсы<br>
                    <span style="color: #8B4513;">в один клик</span>
                </h2>

                <p class="text-warm-gray text-lg leading-relaxed mb-8 max-w-lg">
                    Талант-центр — удобная площадка для образовательных организаций. Создавайте бесплатные конкурсы или получайте внебюджетный доход от платных мероприятий. Всё в одном месте: от приёма заявок до выдачи дипломов.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 mb-10">
                    <a href="{{ route('contests.create') }}"
                        class="inline-flex items-center justify-center px-7 py-3.5 gradient-gold text-dark font-bold rounded-xl text-sm hover:opacity-90 transition-opacity shadow-sm">
                        <i class="fas fa-plus mr-2"></i>Создать конкурс
                    </a>
                    <a href="{{ route('contests.index') }}"
                        class="inline-flex items-center justify-center px-7 py-3.5 border-2 border-primary text-primary font-semibold rounded-xl text-sm hover:bg-primary hover:text-white transition-colors">
                        <i class="fas fa-trophy mr-2"></i>Найти конкурс
                    </a>
                </div>

                {{-- Trust line --}}
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-warm-gray">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-trophy text-gold text-xs"></i>
                        <strong class="text-dark">{{ $stats['contests'] }}+</strong> конкурсов
                    </span>
                    <span class="text-gold/40">·</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-building text-gold text-xs"></i>
                        <strong class="text-dark">{{ $stats['organizations'] }}+</strong> организаций
                    </span>
                    <span class="text-gold/40">·</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-users text-gold text-xs"></i>
                        <strong class="text-dark">{{ $stats['participants'] }}+</strong> участников
                    </span>
                </div>
            </div>

            {{-- Right: Decorative floating cards --}}
            <div class="lg:col-span-2 relative h-[420px] hidden lg:block">
                {{-- Card 3 (back) --}}
                <div class="float-card-3 absolute top-16 right-4 w-52 hero-card p-5 border border-gold/20"
                     style="background: linear-gradient(135deg, #2C2416 0%, #3d3020 100%);">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 gradient-gold rounded-full flex items-center justify-center">
                            <i class="fas fa-award text-white text-xs"></i>
                        </div>
                        <span class="text-xs font-semibold text-gold uppercase tracking-wider">Диплом</span>
                    </div>
                    <div class="h-2 bg-gold/40 rounded mb-2 w-3/4"></div>
                    <div class="h-2 bg-gold/20 rounded mb-2 w-1/2"></div>
                    <div class="mt-4 flex items-center gap-2">
                        <i class="fas fa-star text-gold text-sm"></i>
                        <i class="fas fa-star text-gold text-sm"></i>
                        <i class="fas fa-star text-gold text-sm"></i>
                        <span class="text-xs text-gold/60 ml-1">1 место</span>
                    </div>
                </div>

                {{-- Card 2 (middle) --}}
                <div class="float-card-2 absolute top-36 left-0 w-56 hero-card p-5 border border-primary/10">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-palette text-primary text-sm"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-dark">Изобразительное</div>
                            <div class="text-xs text-warm-gray">искусство</div>
                        </div>
                    </div>
                    <div class="h-1.5 bg-primary/10 rounded mb-1.5 w-full"></div>
                    <div class="h-1.5 bg-primary/10 rounded mb-1.5 w-4/5"></div>
                    <div class="h-1.5 bg-primary/10 rounded w-3/5"></div>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-xs text-warm-gray">42 заявки</span>
                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">Активен</span>
                    </div>
                </div>

                {{-- Card 1 (front) --}}
                <div class="float-card-1 absolute bottom-8 right-8 w-60 hero-card p-5 border border-gold/30"
                     style="background: linear-gradient(135deg, #fffdf7 0%, #fdf6e0 100%);">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 gradient-gold rounded-lg flex items-center justify-center">
                            <i class="fas fa-music text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-dark">Вокальное</div>
                            <div class="text-xs text-warm-gray">мастерство 2025</div>
                        </div>
                    </div>
                    <div class="text-2xl font-serif font-bold text-dark mb-1">24</div>
                    <div class="text-xs text-warm-gray mb-3">дня до конца приёма</div>
                    <a href="{{ route('contests.index') }}" class="text-xs font-semibold text-primary flex items-center gap-1">
                        Подать заявку <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>

                {{-- Decorative dot grid --}}
                <div class="absolute -bottom-4 -left-4 opacity-20" style="width:120px; height:120px;
                    background-image: radial-gradient(#8B4513 1.5px, transparent 1.5px);
                    background-size: 16px 16px;"></div>
            </div>
        </div>
    </div>

    {{-- Bottom wave divider --}}
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 40" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="width:100%; height:40px; display:block;">
            <path d="M0 40 L0 20 Q360 0 720 20 Q1080 40 1440 20 L1440 40 Z" fill="#2C2416"/>
        </svg>
    </div>
</section>

{{-- ===== STATS STRIP ===== --}}
<section class="py-12" style="background: #2C2416;">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4">
            <div class="text-center px-6 py-4 border-b border-r md:border-b-0 border-warm-gray/20">
                <div class="font-serif text-4xl font-bold text-gold mb-1">{{ $stats['contests'] }}<span class="text-2xl align-super text-gold/70">+</span></div>
                <div class="text-xs text-warm-gray uppercase tracking-widest">Конкурсов</div>
            </div>
            <div class="text-center px-6 py-4 border-b md:border-b-0 md:border-r border-warm-gray/20">
                <div class="font-serif text-4xl font-bold text-gold mb-1">{{ $stats['organizations'] }}<span class="text-2xl align-super text-gold/70">+</span></div>
                <div class="text-xs text-warm-gray uppercase tracking-widest">Организаций</div>
            </div>
            <div class="text-center px-6 py-4 border-r border-warm-gray/20">
                <div class="font-serif text-4xl font-bold text-gold mb-1">{{ $stats['participants'] }}<span class="text-2xl align-super text-gold/70">+</span></div>
                <div class="text-xs text-warm-gray uppercase tracking-widest">Участников</div>
            </div>
            <div class="text-center px-6 py-4">
                <div class="font-serif text-4xl font-bold text-gold mb-1">{{ $stats['applications'] }}<span class="text-2xl align-super text-gold/70">+</span></div>
                <div class="text-xs text-warm-gray uppercase tracking-widest">Заявок</div>
            </div>
        </div>
    </div>
</section>

{{-- ===== ACTIVE CONTESTS ===== --}}
<section class="py-20 bg-cream" x-data="{ modal: null }" @keydown.escape.window="modal = null">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <div class="gold-line mb-4"></div>
                <h3 class="font-serif text-3xl sm:text-4xl font-bold text-dark">Активные конкурсы</h3>
                <p class="text-warm-gray mt-2">Открытый приём заявок прямо сейчас</p>
            </div>
            <a href="{{ route('contests.index') }}" class="hidden sm:flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/70 transition-colors">
                Все конкурсы <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        @if($activeContests->count())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activeContests as $contest)
                    @php
                        $contestData = [
                            'title'              => $contest->title,
                            'description'        => $contest->description,
                            'cover_image'        => $contest->cover_image ? asset('storage/' . $contest->cover_image) : null,
                            'category'           => $contest->platformCategory?->name,
                            'org_name'           => $contest->organization->name,
                            'applications_start' => $contest->applications_start_at->isoFormat('D MMM YYYY'),
                            'applications_end'   => $contest->applications_end_at->isoFormat('D MMM YYYY'),
                            'evaluation_end'     => $contest->evaluation_end_at->isoFormat('D MMM YYYY'),
                            'apply_url'          => route('applications.create', $contest),
                            'show_url'           => route('contests.show', $contest),
                            'categories'         => $contest->categories->map(fn ($c) => $c->name)->values()->toArray(),
                        ];
                    @endphp
                    <div @click="modal = {{ \Illuminate\Support\Js::from($contestData) }}"
                        class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-gold/10 hover-lift flex flex-col cursor-pointer">

                        {{-- Cover or placeholder --}}
                        <div class="relative" style="padding-top: 56.25%;">
                            @if($contest->cover_image)
                                <img src="{{ asset('storage/' . $contest->cover_image) }}"
                                    alt="{{ $contest->title }}"
                                    class="absolute inset-0 w-full h-full object-cover">
                            @else
                                <div class="absolute inset-0 gradient-gold flex items-center justify-center">
                                    <i class="fas fa-award text-white/25 text-5xl"></i>
                                </div>
                            @endif
                            {{-- Category badge --}}
                            @if($contest->platformCategory)
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 bg-dark/80 text-gold text-xs font-semibold rounded-full backdrop-blur-sm">
                                        {{ $contest->platformCategory->name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 flex flex-col flex-1">
                            {{-- Org --}}
                            <div class="flex items-center gap-2 mb-3">
                                <x-org-avatar :organization="$contest->organization" size="xs" />
                                <span class="text-xs text-warm-gray truncate">{{ $contest->organization->name }}</span>
                            </div>

                            {{-- Title --}}
                            <h4 class="font-serif font-bold text-dark text-lg leading-snug mb-2 group-hover:text-primary transition-colors">
                                {{ $contest->title }}
                            </h4>

                            {{-- Description --}}
                            @if($contest->description)
                                <p class="text-xs text-warm-gray mb-3 line-clamp-2 leading-relaxed flex-1">{{ $contest->description }}</p>
                            @else
                                <div class="flex-1"></div>
                            @endif

                            {{-- Footer --}}
                            <div class="flex items-center justify-between pt-3 border-t border-gold/10">
                                <div class="flex items-center gap-1.5 text-xs text-warm-gray">
                                    <i class="fas fa-calendar-alt text-gold"></i>
                                    до {{ $contest->applications_end_at->isoFormat('D MMM') }}
                                </div>
                                <span class="text-xs font-semibold text-primary group-hover:underline flex items-center gap-1">
                                    Подробнее <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-8 sm:hidden">
                <a href="{{ route('contests.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary">
                    Все конкурсы <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        @else
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full border-2 border-gold/30 mb-6">
                    <i class="fas fa-trophy text-3xl text-gold/50"></i>
                </div>
                <p class="font-serif text-xl text-warm-gray">Скоро появятся новые конкурсы</p>
                <div class="gold-line mx-auto mt-4"></div>
            </div>
        @endif
    </div>

    {{-- ===== CONTEST MODAL ===== --}}
    <div
        x-show="modal"
        x-cloak
        @click.self="modal = null"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
        <div
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white rounded-2xl max-w-lg w-full shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden"
        >
            {{-- Cover / header --}}
            <div class="relative h-40 shrink-0 overflow-hidden">
                <img
                    x-show="modal && modal.cover_image"
                    :src="modal ? (modal.cover_image || '') : ''"
                    class="w-full h-full object-cover"
                    style="display: none;"
                    alt=""
                >
                <div
                    x-show="!modal || !modal.cover_image"
                    class="w-full h-full gradient-gold flex items-center justify-center"
                >
                    <i class="fas fa-award text-white/25 text-6xl"></i>
                </div>

                <button
                    @click="modal = null"
                    class="absolute top-3 right-3 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center text-dark shadow-sm transition-colors"
                >
                    <i class="fas fa-times text-sm"></i>
                </button>

                <template x-if="modal && modal.category">
                    <span
                        class="absolute bottom-3 left-3 bg-white text-dark text-xs font-medium px-3 py-1 rounded-full shadow-sm border border-gold/10"
                        x-text="modal.category"
                    ></span>
                </template>
            </div>

            {{-- Scrollable content --}}
            <div class="overflow-y-auto flex-1 p-6">

                <h2
                    class="font-serif text-2xl font-bold text-dark mb-1 leading-tight"
                    x-text="modal ? modal.title : ''"
                ></h2>

                <p
                    class="text-sm text-warm-gray mb-4"
                    x-text="modal ? modal.org_name : ''"
                ></p>

                <template x-if="modal && modal.description">
                    <p
                        class="text-dark text-sm leading-relaxed mb-5 whitespace-pre-line"
                        x-text="modal.description"
                    ></p>
                </template>

                <div class="bg-cream rounded-xl p-4 space-y-2 mb-4 text-sm">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-calendar-alt text-primary w-4 text-center shrink-0"></i>
                        <span class="text-warm-gray shrink-0">Приём заявок:</span>
                        <span
                            class="font-medium text-dark"
                            x-text="modal ? (modal.applications_start + ' — ' + modal.applications_end) : ''"
                        ></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-flag-checkered text-primary w-4 text-center shrink-0"></i>
                        <span class="text-warm-gray shrink-0">Результаты:</span>
                        <span
                            class="font-medium text-dark"
                            x-text="modal ? modal.evaluation_end : ''"
                        ></span>
                    </div>
                </div>

                <template x-if="modal && modal.categories && modal.categories.length">
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">Номинации</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="cat in modal.categories" :key="cat">
                                <span
                                    class="text-xs px-3 py-1 bg-gold/10 text-dark rounded-full border border-gold/20"
                                    x-text="cat"
                                ></span>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex gap-3 pt-2">
                    @auth
                        <a
                            :href="modal ? modal.apply_url : '#'"
                            class="flex-1 text-center px-4 py-3 gradient-gold text-dark font-semibold rounded-xl hover:opacity-90 transition-opacity"
                        >
                            <i class="fas fa-paper-plane mr-2"></i>Участвовать
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="flex-1 text-center px-4 py-3 gradient-gold text-dark font-semibold rounded-xl hover:opacity-90 transition-opacity"
                        >
                            <i class="fas fa-paper-plane mr-2"></i>Участвовать
                        </a>
                    @endauth
                    <a
                        :href="modal ? modal.show_url : '#'"
                        class="flex-1 text-center px-4 py-3 border border-primary/20 text-primary rounded-xl hover:bg-primary/5 transition-colors"
                    >
                        Подробнее
                    </a>
                </div>

            </div>
        </div>
    </div>

</section>

{{-- ===== HOW IT WORKS — split design ===== --}}
<section class="overflow-hidden">
    <div class="grid md:grid-cols-2">

        {{-- Left: Participants (cream) --}}
        <div class="py-16 px-8 lg:px-16" style="background: #FAF8F5;">
            <div class="max-w-md ml-auto">
                <div class="gold-line mb-6"></div>
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-10">Для участников</h3>
                <div class="space-y-8">
                    <div class="flex gap-5">
                        <div class="step-number text-primary/15 shrink-0 leading-none" style="font-size: 4rem;">1</div>
                        <div class="pt-2">
                            <h4 class="font-semibold text-dark mb-1.5">Зарегистрируйтесь</h4>
                            <p class="text-sm text-warm-gray leading-relaxed">Создайте личный профиль за несколько минут. Добавьте детей или подопечных, если подаёте заявки от их имени.</p>
                        </div>
                    </div>
                    <div class="flex gap-5">
                        <div class="step-number text-primary/15 shrink-0 leading-none" style="font-size: 4rem;">2</div>
                        <div class="pt-2">
                            <h4 class="font-semibold text-dark mb-1.5">Выберите конкурс</h4>
                            <p class="text-sm text-warm-gray leading-relaxed">Найдите подходящий конкурс по категории и подайте заявку — прикрепите файл или ссылку на работу.</p>
                        </div>
                    </div>
                    <div class="flex gap-5">
                        <div class="step-number text-primary/15 shrink-0 leading-none" style="font-size: 4rem;">3</div>
                        <div class="pt-2">
                            <h4 class="font-semibold text-dark mb-1.5">Получите диплом</h4>
                            <p class="text-sm text-warm-gray leading-relaxed">По итогам оценки жюри вы получите официальный диплом в PDF — его можно распечатать или добавить в портфолио.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-10">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 gradient-gold text-dark font-bold rounded-xl text-sm hover:opacity-90 transition">
                        <i class="fas fa-user-plus"></i> Зарегистрироваться
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Organizers (dark) --}}
        <div class="py-16 px-8 lg:px-16" style="background: #2C2416;">
            <div class="max-w-md mr-auto">
                <div class="gold-line mb-6"></div>
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-gold mb-10">Для организаторов</h3>
                <div class="space-y-8">
                    <div class="flex gap-5">
                        <div class="step-number shrink-0 leading-none" style="font-size: 4rem; color: rgba(212,175,55,0.15);">1</div>
                        <div class="pt-2">
                            <h4 class="font-semibold text-cream mb-1.5">Создайте организацию</h4>
                            <p class="text-sm text-warm-gray leading-relaxed">Зарегистрируйте организацию и пройдите верификацию. После одобрения вы сможете публиковать конкурсы.</p>
                        </div>
                    </div>
                    <div class="flex gap-5">
                        <div class="step-number shrink-0 leading-none" style="font-size: 4rem; color: rgba(212,175,55,0.15);">2</div>
                        <div class="pt-2">
                            <h4 class="font-semibold text-cream mb-1.5">Оформите конкурс</h4>
                            <p class="text-sm text-warm-gray leading-relaxed">Задайте номинации, дедлайны и требования к работам. Пригласите членов жюри к оценке.</p>
                        </div>
                    </div>
                    <div class="flex gap-5">
                        <div class="step-number shrink-0 leading-none" style="font-size: 4rem; color: rgba(212,175,55,0.15);">3</div>
                        <div class="pt-2">
                            <h4 class="font-semibold text-cream mb-1.5">Выдайте дипломы</h4>
                            <p class="text-sm text-warm-gray leading-relaxed">Оцените заявки, назначьте места — платформа автоматически сформирует именные дипломы для всех участников.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-10">
                    <a href="{{ route('organizations.create') }}" class="inline-flex items-center gap-2 px-6 py-3 border-2 border-gold text-gold font-bold rounded-xl text-sm hover:bg-gold hover:text-dark transition">
                        <i class="fas fa-building"></i> Создать организацию
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== MISSION QUOTE ===== --}}
<section class="py-20 bg-cream overflow-hidden">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative">
            {{-- Giant decorative quote mark --}}
            <div class="absolute -top-8 -left-4 font-serif text-gold/10 select-none leading-none"
                 style="font-size: 16rem; line-height: 1;">"</div>

            <div class="relative grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="font-serif text-2xl sm:text-3xl italic font-medium text-dark leading-relaxed">
                        Каждый талант достоин сцены — мы создаём пространство, где мастерство получает признание, а усилия — награду.
                    </p>
                    <div class="mt-8 flex items-center gap-3">
                        <div class="w-10 h-10 gradient-gold rounded-full flex items-center justify-center">
                            <i class="fas fa-award text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-dark text-sm">Талант-центр</div>
                            <div class="text-xs text-warm-gray">Всероссийская платформа конкурсов</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="gold-line mb-6"></div>
                    <div class="flex items-start gap-4 p-4 rounded-xl bg-white border border-gold/10">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-shield-alt text-primary"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-dark text-sm mb-0.5">Прозрачность</div>
                            <div class="text-xs text-warm-gray">Открытые правила, понятные критерии оценки и честные результаты.</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 p-4 rounded-xl bg-white border border-gold/10">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-globe text-primary"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-dark text-sm mb-0.5">Доступность</div>
                            <div class="text-xs text-warm-gray">Участвуйте из любой точки России — без ограничений по географии.</div>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 p-4 rounded-xl bg-white border border-gold/10">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-star text-primary"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-dark text-sm mb-0.5">Профессионализм</div>
                            <div class="text-xs text-warm-gray">Официальные дипломы и признанные организаторы на одной платформе.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== CTA ===== --}}
<section class="pattern-bg" style="background-color: #2C2416; padding: 80px 0;">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 mb-6">
            <div class="w-8 h-px bg-gold"></div>
            <i class="fas fa-award text-gold text-sm"></i>
            <div class="w-8 h-px bg-gold"></div>
        </div>

        <h3 class="font-serif text-3xl sm:text-4xl font-bold text-cream mb-4">
            Начните прямо сейчас
        </h3>

        @auth
            <p class="text-warm-gray text-lg mb-8">
                Добро пожаловать обратно! Перейдите в личный кабинет, чтобы продолжить.
            </p>
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 px-8 py-4 gradient-gold text-dark font-bold rounded-xl text-sm hover:opacity-90 transition shadow-lg">
                <i class="fas fa-th-large"></i> Личный кабинет
            </a>
        @else
            <p class="text-warm-gray text-lg mb-8">
                Зарегистрируйтесь бесплатно — для участия в конкурсах, организации мероприятий или работы в жюри.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('register') }}"
                    class="inline-flex items-center gap-2 px-8 py-4 gradient-gold text-dark font-bold rounded-xl text-sm hover:opacity-90 transition shadow-lg">
                    <i class="fas fa-user-plus"></i> Зарегистрироваться
                </a>
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 px-8 py-4 border-2 border-gold/50 text-gold font-semibold rounded-xl text-sm hover:border-gold hover:bg-gold/10 transition">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </a>
            </div>
        @endauth
    </div>
</section>

{{-- ===== FOOTER ===== --}}
<footer class="bg-dark text-cream py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div class="md:col-span-1">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 gradient-gold rounded-full flex items-center justify-center">
                        <i class="fas fa-award text-white"></i>
                    </div>
                    <div>
                        <h4 class="font-serif font-bold text-cream">Талант-центр</h4>
                    </div>
                </div>
                <p class="text-warm-gray text-sm leading-relaxed">
                    Всероссийская платформа для проведения онлайн-конкурсов, оценки работ и выдачи дипломов.
                </p>
            </div>

            <div>
                <h5 class="font-serif font-semibold text-gold mb-4">Разделы</h5>
                <ul class="space-y-2 text-sm">
                    <li><a href="/" class="text-warm-gray hover:text-gold transition-colors">Главная</a></li>
                    <li><a href="{{ route('contests.index') }}" class="text-warm-gray hover:text-gold transition-colors">Конкурсы</a></li>
                    <li><a href="{{ route('development-plan') }}" class="text-warm-gray hover:text-gold transition-colors">План развития</a></li>
                </ul>
            </div>

            <div>
                <h5 class="font-serif font-semibold text-gold mb-4">Организаторам</h5>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('organizations.create') }}" class="text-warm-gray hover:text-gold transition-colors">Создать организацию</a></li>
                    <li><a href="{{ route('contests.create') }}" class="text-warm-gray hover:text-gold transition-colors">Создать конкурс</a></li>
                    <li><a href="{{ route('development-plan') }}" class="text-warm-gray hover:text-gold transition-colors">Документация</a></li>
                </ul>
            </div>

            <div>
                <h5 class="font-serif font-semibold text-gold mb-4">Контакты</h5>
                <ul class="space-y-2 text-sm">
                    <li class="text-warm-gray"><i class="fas fa-envelope text-gold mr-2"></i>info@talant-centr.ru</li>
                    <li class="text-warm-gray"><i class="fas fa-phone text-gold mr-2"></i>+7 (800) 000-00-00</li>
                    <li class="text-warm-gray"><i class="fas fa-map-marker-alt text-gold mr-2"></i>Россия</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-warm-gray/20 pt-6">
            <p class="text-warm-gray text-sm text-center">
                &copy; {{ date('Y') }} Талант-центр. Все права защищены.
            </p>
        </div>
    </div>
</footer>

</body>
</html>
