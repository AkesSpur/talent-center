<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Талант-центр'))</title>

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

    @yield('head')
</head>
<body class="font-sans antialiased bg-cream text-dark @yield('body-class')">

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
                    <a href="/"
                        class="text-sm font-medium {{ request()->routeIs('home') ? 'text-primary border-b-2 border-primary pb-0.5' : 'text-warm-gray hover:text-primary transition-colors' }}">
                        Главная
                    </a>
                    <a href="{{ route('contests.index') }}"
                        class="text-sm font-medium {{ request()->routeIs('contests.index') ? 'text-primary border-b-2 border-primary pb-0.5' : 'text-warm-gray hover:text-primary transition-colors' }}">
                        Конкурсы
                    </a>
                    {{-- <a href="{{ route('diplomvtrifi.search') }}"
                        class="text-sm font-medium {{ request()->routeIs('diplomvtrifi.*') ? 'text-primary border-b-2 border-primary pb-0.5' : 'text-warm-gray hover:text-primary transition-colors' }}">
                        Проверить диплом
                    </a> --}}
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

    @yield('content')

    @include('layouts.footer')

</body>
</html>
