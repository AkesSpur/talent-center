<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Проверить диплом — Талант-центр</title>

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
        @keyframes shrink {
            from { width: 100%; }
            to   { width: 0%; }
        }
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

    {{-- Toast notification --}}
    @if(session('error'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="fixed top-6 right-6 z-50 max-w-sm w-full"
        style="display: none;"
    >
        <div class="bg-white border border-red-200 rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-start gap-3 p-4">
                <div class="w-9 h-9 bg-red-100 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas fa-times-circle text-red-500 text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-dark mb-0.5">Диплом не найден</p>
                    <p class="text-sm text-warm-gray leading-snug">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="shrink-0 text-warm-gray hover:text-dark transition-colors mt-0.5">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="h-1 bg-red-100">
                <div class="h-1 bg-red-400 animate-[shrink_5s_linear_forwards]" style="width:100%"></div>
            </div>
        </div>
    </div>
    @endif

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-8 sm:py-10 px-4 text-center">
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 gradient-gold rounded-full flex items-center justify-center shadow-md">
                <i class="fas fa-certificate text-white text-2xl"></i>
            </div>
        </div>
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-dark mb-3">
            Проверить диплом
        </h2>
        <p class="text-warm-gray text-md max-w-xl mx-auto">
            Введите номер диплома для проверки подлинности и получения информации о награде
        </p>
    </section>

    <!-- ========== MAIN ========== -->
    <main class="flex-1 py-12 px-4">
        <div class="max-w-lg mx-auto">

            <div class="bg-white rounded-2xl shadow-sm border border-gold/10 p-8">
                <form method="POST" action="{{ route('diplomvtrifi.find') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="diploma_number" class="block text-sm font-semibold text-dark mb-2">
                            <i class="fas fa-hashtag text-gold mr-1.5"></i>Номер диплома
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="diploma_number"
                                name="diploma_number"
                                value="{{ old('diploma_number') }}"
                                placeholder="Например: 1-0000042"
                                class="w-full border rounded-xl px-4 py-3 pr-10 text-dark placeholder-warm-gray focus:outline-none focus:ring-2 focus:ring-gold/40 focus:border-gold transition-colors {{ session('error') || $errors->has('diploma_number') ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }}"
                                autofocus
                            >
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                                <i class="fas fa-search {{ session('error') || $errors->has('diploma_number') ? 'text-red-400' : 'text-warm-gray' }}"></i>
                            </div>
                        </div>
                        @error('diploma_number')
                            <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-warm-gray">
                            <i class="fas fa-info-circle mr-1"></i>Номер диплома указан в нижней части документа
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full gradient-gold text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-search"></i>
                        Найти диплом
                    </button>
                </form>
            </div>

            <!-- Info card -->
            <div class="mt-6 bg-white rounded-2xl border border-gold/10 shadow-sm p-6">
                <h3 class="font-serif font-semibold text-dark mb-3 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-gold"></i>
                    Зачем проверять диплом?
                </h3>
                <ul class="space-y-2 text-sm text-warm-gray">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-gold mt-0.5 shrink-0"></i>
                        Убедитесь в подлинности документа
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-gold mt-0.5 shrink-0"></i>
                        Получите полную информацию о награде и конкурсе
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-gold mt-0.5 shrink-0"></i>
                        Подтвердите данные участника и организатора
                    </li>
                </ul>
            </div>

        </div>
    </main>

    <!-- ========== FOOTER ========== -->
    @include('layouts.footer')

</body>
</html>
