<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'Платформа для участия в онлайн-конкурсах')">

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
                <div class="flex items-center justify-end space-x-4 ml-auto lg:ml-0">
                    @auth
                        {{-- The full menu lives in the cabinet sidebar; this is the way in. --}}
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-primary/20 py-1 pl-1 pr-1 text-sm font-medium text-dark transition hover:bg-primary/5 active:scale-[0.98] sm:pr-4"
                            title="Личный кабинет">
                            <x-user-avatar :user="Auth::user()" size="sm" />
                            <span class="hidden sm:inline">Личный кабинет</span>
                        </a>
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
