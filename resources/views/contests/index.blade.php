<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Конкурсы — Талант-центр</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-cream text-dark">

    <!-- ========== HEADER ========== -->
    <header class="bg-cream shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">

                <!-- Logo (left) -->
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-11 h-11 gradient-gold rounded-full flex items-center justify-center shadow-sm shrink-0">
                        <i class="fas fa-award text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="font-serif text-lg font-bold text-primary leading-tight">Талант-центр</h1>
                        <p class="text-xs text-warm-gray leading-tight">Всероссийский центр талантов</p>
                    </div>
                </a>

                <!-- Center Nav (large screens only) -->
                <nav class="hidden lg:flex items-center justify-center gap-8">
                    <a href="/" class="text-sm font-medium text-warm-gray hover:text-primary transition-colors">
                        Главная
                    </a>
                    <a href="{{ route('contests.index') }}" class="text-sm font-medium text-primary border-b-2 border-primary pb-0.5">
                        Конкурсы
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
                                            <i class="fas fa-tags mr-2 text-warm-gray w-5 text-center"></i> Категории
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
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-dark mb-4">
            Все конкурсы
        </h2>
        <p class="text-warm-gray text-md max-w-2xl mx-auto">
            Найдите конкурс по своим интересам и участвуйте в творческих состязаниях
        </p>
    </section>

    <!-- ========== MAIN CONTENT ========== -->
    <main
        x-data="{
            search: '',
            category: '',
            modal: null,
            check(title, catId) {
                const ms = this.search === '' || title.toLowerCase().includes(this.search.toLowerCase());
                const mc = this.category === '' || String(catId) === this.category;
                return ms && mc;
            }
        }"
        @keydown.escape.window="modal = null"
        class="py-10"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Filter bar --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gold/10 p-4 flex flex-col sm:flex-row gap-3">
                {{-- Search --}}
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-warm-gray text-sm pointer-events-none"></i>
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Поиск по названию..."
                        class="w-full pl-10 pr-4 py-2.5 border border-primary/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm bg-cream/50"
                    />
                </div>

                {{-- Category --}}
                @if($platformCategories->count())
                    <div class="sm:w-64">
                        <select
                            x-model="category"
                            class="w-full px-3 py-2.5 border border-primary/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm bg-cream/50 text-dark"
                        >
                            <option value="">Все категории</option>
                            @foreach($platformCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            {{-- Contest grid --}}
            @if($contests->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($contests as $contest)
                        @php
                            $contestData = [
                                'title'              => $contest->title,
                                'description'        => $contest->description,
                                'cover_image'        => $contest->cover_image ? asset('storage/' . $contest->cover_image) : null,
                                'category'           => $contest->platformCategory?->name,
                                'org_name'           => $contest->organization->name,
                                'applications_start' => $contest->applications_start_at->format('d M'),
                                'applications_end'   => $contest->applications_end_at->format('d M'),
                                'evaluation_end'     => $contest->evaluation_end_at->format('d M'),
                                'apply_url'          => route('applications.create', $contest),
                                'show_url'           => route('contests.show', $contest),
                                'categories'         => $contest->categories->map(fn ($c) => $c->name)->values()->toArray(),
                            ];
                        @endphp
                        <div
                            x-show="check({{ Js::from($contest->title) }}, '{{ $contest->platform_category_id ?? '' }}')"
                            @click="modal = {{ Js::from($contestData) }}"
                            class="bg-white rounded-2xl shadow-sm border border-gold/10 hover-lift flex flex-col overflow-hidden cursor-pointer"
                        >
                            {{-- Cover area --}}
                            <div class="relative h-44 overflow-hidden shrink-0">
                                @if($contest->cover_image)
                                    <img src="{{ asset('storage/' . $contest->cover_image) }}"
                                        class="w-full h-full object-cover" alt="{{ $contest->title }}">
                                @else
                                    <div class="w-full h-full gradient-gold flex items-center justify-center">
                                        <i class="fas fa-award text-white/25 text-7xl"></i>
                                    </div>
                                @endif

                                {{-- Category badge overlaid --}}
                                @if($contest->platformCategory)
                                    <span class="absolute top-3 right-3 bg-white text-dark text-xs font-medium px-3 py-1 rounded-full shadow-sm border border-gold/10">
                                        {{ $contest->platformCategory->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Card body --}}
                            <div class="p-5 flex flex-col flex-1">

                                {{-- Title --}}
                                <h3 class="font-serif text-lg font-bold text-dark mb-1 leading-snug group-hover:text-primary transition-colors">
                                    {{ $contest->title }}
                                </h3>

                                {{-- Description excerpt --}}
                                @if($contest->description)
                                    <p class="text-warm-gray text-sm mb-3 line-clamp-2 leading-relaxed">{{ $contest->description }}</p>
                                @endif

                                {{-- Dates --}}
                                <div class="text-xs text-warm-gray space-y-1 mb-4">
                                    <div>
                                        <i class="fas fa-calendar-alt mr-1.5"></i>
                                        {{ $contest->applications_start_at->format('d M') }} — {{ $contest->applications_end_at->format('d M') }}
                                    </div>
                                    <div>
                                        <i class="fas fa-flag-checkered mr-1.5"></i>
                                        Результаты: {{ $contest->evaluation_end_at->format('d M') }}
                                    </div>
                                </div>

                                {{-- Org row --}}
                                <div class="mt-auto pt-3 border-t border-gold/10 flex items-center gap-2">
                                    <x-org-avatar :organization="$contest->organization" size="xs" />
                                    <span class="text-sm text-dark font-medium truncate flex-1">{{ $contest->organization->name }}</span>
                                    <i class="fas fa-chevron-right text-xs text-warm-gray shrink-0"></i>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                <div>{{ $contests->links() }}</div>

            @else
                <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-trophy text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Конкурсов пока нет</h3>
                    <p class="text-warm-gray">Загляните позже — новые конкурсы появятся здесь.</p>
                </div>
            @endif

        </div>

        {{-- ========== CONTEST MODAL ========== --}}
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

                    {{-- Close button --}}
                    <button
                        @click="modal = null"
                        class="absolute top-3 right-3 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center text-dark shadow-sm transition-colors"
                    >
                        <i class="fas fa-times text-sm"></i>
                    </button>

                    {{-- Category badge --}}
                    <template x-if="modal && modal.category">
                        <span
                            class="absolute bottom-3 left-3 bg-white text-dark text-xs font-medium px-3 py-1 rounded-full shadow-sm border border-gold/10"
                            x-text="modal.category"
                        ></span>
                    </template>
                </div>

                {{-- Scrollable content --}}
                <div class="overflow-y-auto flex-1 p-6">

                    {{-- Title --}}
                    <h2
                        class="font-serif text-2xl font-bold text-dark mb-1 leading-tight"
                        x-text="modal ? modal.title : ''"
                    ></h2>

                    {{-- Org --}}
                    <p
                        class="text-sm text-warm-gray mb-4"
                        x-text="modal ? modal.org_name : ''"
                    ></p>

                    {{-- Description --}}
                    <template x-if="modal && modal.description">
                        <p
                            class="text-dark text-sm leading-relaxed mb-5 whitespace-pre-line"
                            x-text="modal.description"
                        ></p>
                    </template>

                    {{-- Dates --}}
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

                    {{-- Nominations --}}
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

                    {{-- Action buttons --}}
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

    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="bg-dark text-cream py-12 px-4 mt-12">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="md:col-span-1">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 gradient-gold rounded-full flex items-center justify-center">
                            <i class="fas fa-award text-white"></i>
                        </div>
                        <h4 class="font-serif font-bold text-cream">Талант-центр</h4>
                    </div>
                    <p class="text-warm-gray text-sm">
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
                        <li><a href="{{ route('contests.create') }}" class="text-warm-gray hover:text-gold transition-colors">Провести конкурс</a></li>
                        <li><a href="#" class="text-warm-gray hover:text-gold transition-colors">Документация</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="font-serif font-semibold text-gold mb-4">Контакты</h5>
                    <ul class="space-y-2 text-sm">
                        <li class="text-warm-gray"><i class="fas fa-envelope text-gold mr-2"></i>info@talentcenter.ru</li>
                        <li class="text-warm-gray"><i class="fas fa-phone text-gold mr-2"></i>+7 (800) 000-00-00</li>
                        <li class="text-warm-gray"><i class="fas fa-map-marker-alt text-gold mr-2"></i>Россия</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-warm-gray/30 pt-6">
                <p class="text-warm-gray text-sm text-center">
                    &copy; {{ date('Y') }} Талант-центр. Все права защищены.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
