<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Диплом № {{ $diploma->diploma_number }} — Талант-центр</title>

    <!-- Favicon -->
    @if(!empty($siteSettings[\App\Models\SiteSettings::SITE_FAVICON]))
        <link rel="icon" href="{{ asset('storage/' . $siteSettings[\App\Models\SiteSettings::SITE_FAVICON]) }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-cream text-dark min-h-screen flex flex-col">

    <!-- ========== HEADER ========== -->
    <header class="bg-cream shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">

                <!-- Logo (left) -->
                <a href="/" class="flex items-center space-x-3">
                    @if(!empty($siteSettings[\App\Models\SiteSettings::SITE_LOGO]))
                        <img src="{{ asset('storage/' . $siteSettings[\App\Models\SiteSettings::SITE_LOGO]) }}"
                             alt="Талант-центр" class="h-11 w-auto max-w-[44px] object-contain shrink-0">
                    @else
                        <div class="w-11 h-11 gradient-gold rounded-full flex items-center justify-center shadow-sm shrink-0">
                            <i class="fas fa-award text-white text-lg"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="font-serif text-lg font-bold leading-tight" style="color: {{ $siteSettings[\App\Models\SiteSettings::SITE_NAME_COLOR] ?? '#8B4513' }}">{{ $siteSettings[\App\Models\SiteSettings::SITE_NAME] ?? 'Талант-центр' }}</h1>
                        <p class="text-xs leading-tight" style="color: {{ $siteSettings[\App\Models\SiteSettings::SITE_SUBTITLE_COLOR] ?? '#9A8B7A' }}">{{ $siteSettings[\App\Models\SiteSettings::SITE_SUBTITLE] ?? 'Всероссийский центр талантов' }}</p>
                    </div>
                </a>

                <!-- Center Nav (large screens only) -->
                <nav class="hidden lg:flex items-center justify-center gap-8">
                    <a href="/" class="text-sm font-medium text-warm-gray hover:text-primary transition-colors">
                        Главная
                    </a>
                    <a href="{{ route('contests.index') }}" class="text-sm font-medium text-warm-gray hover:text-primary transition-colors">
                        Конкурсы
                    </a>
                    <a href="{{ route('diplomvtrifi.search') }}" class="text-sm font-medium text-primary border-b-2 border-primary pb-0.5">
                        Проверить диплом
                    </a>
                </nav>

                <!-- Right Side: User / Auth -->
                <div class="flex items-center justify-end space-x-4 ml-auto lg:ml-0" x-data="{ open: false }">
                    @auth
                        <div class="relative">
                            <button @click="open = !open"
                                class="inline-flex items-center space-x-2 px-3 py-2 text-sm font-medium text-dark hover:text-primary focus:outline-none transition duration-150">
                                <x-user-avatar :user="Auth::user()" size="sm" />
                                <span class="hidden sm:inline">{{ Auth::user()->last_name }} {{ mb_substr(Auth::user()->first_name, 0, 1) }}.{{ Auth::user()->patronymic ? mb_substr(Auth::user()->patronymic, 0, 1) . '.' : '' }}</span>
                                <i class="fas fa-chevron-down text-xs text-warm-gray"></i>
                            </button>

                            <div x-show="open" @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 z-50 mt-2 w-72 rounded-xl shadow-lg origin-top-right ring-1 ring-gold/20 bg-white py-1"
                                 style="display: none;">

                                <div class="px-4 py-2 text-xs font-semibold text-warm-gray uppercase tracking-wider">Участник конкурсов</div>
                                <a href="{{ route('profile.edit') }}"
                                    class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                    <i class="fas fa-user-circle mr-2 text-warm-gray w-5 text-center"></i> Профиль представителя
                                </a>
                                <a href="{{ route('dashboard.applications') }}"
                                    class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                    <i class="fas fa-file-alt mr-2 text-warm-gray w-5 text-center"></i> Заявки
                                </a>
                                <a href="#"
                                    class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                    <i class="fas fa-trophy mr-2 text-warm-gray w-5 text-center"></i> Награды
                                </a>
                                <a href="{{ route('participants.index') }}"
                                    class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                    <i class="fas fa-users mr-2 text-warm-gray w-5 text-center"></i> Участники
                                </a>

                                <div class="border-t border-gold/10 mt-1 pt-1">
                                    <div class="px-4 py-2 text-xs font-semibold text-warm-gray uppercase tracking-wider">Организатор конкурсов</div>
                                    <a href="{{ route('dashboard.contests') }}"
                                        class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                        <i class="fas fa-trophy mr-2 text-warm-gray w-5 text-center"></i> Конкурсы
                                    </a>
                                    <a href="{{ route('organizations.index') }}"
                                        class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                        <i class="fas fa-sitemap mr-2 text-warm-gray w-5 text-center"></i> Управление организацией
                                    </a>
                                </div>

                                @if(auth()->user()->isAdmin())
                                    <div class="border-t border-gold/10 mt-1 pt-1">
                                        <div class="px-4 py-2 text-xs font-semibold text-warm-gray uppercase tracking-wider">Администрирование</div>
                                        <a href="{{ route('admin.dashboard') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-cog mr-2 text-warm-gray w-5 text-center"></i> Админ-панель
                                        </a>
                                        <a href="{{ route('admin.users.index') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-users mr-2 text-warm-gray w-5 text-center"></i> Пользователи
                                        </a>
                                        <a href="{{ route('admin.organizations.index') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-sitemap mr-2 text-warm-gray w-5 text-center"></i> Организации
                                        </a>
                                        <a href="{{ route('admin.contests.index') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-trophy mr-2 text-warm-gray w-5 text-center"></i> Конкурсы
                                        </a>
                                        <a href="{{ route('admin.platform-categories.index') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-tags mr-2 text-warm-gray w-5 text-center"></i> Жанры
                                        </a>
                                    </div>
                                @endif

                                @if(auth()->user()->isSupport())
                                    <div class="border-t border-gold/10 mt-1 pt-1">
                                        <div class="px-4 py-2 text-xs font-semibold text-warm-gray uppercase tracking-wider">Поддержка</div>
                                        <a href="{{ route('support.dashboard') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-headset mr-2 text-warm-gray w-5 text-center"></i> Панель поддержки
                                        </a>
                                        <a href="{{ route('support.users.index') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-users mr-2 text-warm-gray w-5 text-center"></i> Пользователи
                                        </a>
                                        <a href="{{ route('support.organizations.index') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-sitemap mr-2 text-warm-gray w-5 text-center"></i> Организации
                                        </a>
                                        <a href="{{ route('support.contests.index') }}"
                                            class="block w-full px-4 py-2 text-start text-sm text-dark hover:bg-cream transition duration-150">
                                            <i class="fas fa-trophy mr-2 text-warm-gray w-5 text-center"></i> Конкурсы
                                        </a>
                                    </div>
                                @endif

                                <div class="border-t border-gold/10 mt-1 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full px-4 py-2 text-start text-sm text-red-600 hover:bg-red-50 transition duration-150">
                                            <i class="fas fa-sign-out-alt mr-2 w-5 text-center"></i> Выйти
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="px-4 py-2 text-primary font-medium text-sm transition hover:text-primary/80">
                                Войти
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="hidden sm:inline-block px-6 py-2 gradient-gold text-dark font-semibold rounded-lg text-sm hover:opacity-90 transition">
                                Регистрация
                            </a>
                        @endif
                    @endauth
                </div>

            </div>
        </div>
    </header>

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

    <!-- ========== FOOTER ========== -->
    @include('layouts.footer')

</body>
</html>
