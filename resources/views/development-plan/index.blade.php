<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>План развития — {{ config('app.name', 'Талант-центр') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-cream text-dark">

    <!-- ========== HEADER ========== -->
    <header class="bg-cream shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-12 h-12 gradient-gold rounded-full flex items-center justify-center shadow-sm">
                        <i class="fas fa-award text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="font-serif text-xl font-bold text-primary">Талант-центр</h1>
                        <p class="text-xs text-warm-gray">Всероссийский центр талантов</p>
                    </div>
                </a>

                <!-- Auth buttons -->
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-primary hover:text-primary-dark font-medium text-sm transition">
                            <i class="fas fa-th-large mr-1"></i> Личный кабинет
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="px-4 py-2 text-primary hover:text-primary-dark font-medium text-sm transition">
                                Войти
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="hidden sm:inline-block px-6 py-2 gradient-gold text-dark font-semibold rounded-lg text-sm hover:opacity-90 transition">
                                Регистрация
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-16 sm:py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Вернуться на главную
            </a>

            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-dark mb-6">
                План развития
            </h2>

            <p class="text-lg text-warm-gray max-w-2xl mx-auto mb-4">
                Платформа «Талант-центр» создаётся в 4 этапа. Здесь вы можете отслеживать прогресс разработки и ознакомиться с подробным отчётом по каждому завершённому этапу.
            </p>

            <div class="inline-flex items-center px-4 py-2 rounded-full bg-gold/10 border border-gold/30 text-sm text-primary font-medium">
                <i class="fas fa-ruble-sign mr-2 text-gold"></i>
                Общий бюджет: 120 000 ₽
            </div>
        </div>
    </section>

    <!-- ========== TIMELINE ========== -->
    <section class="py-16 sm:py-20 px-4">
        <div class="max-w-3xl mx-auto">

            <!-- Timeline -->
            <div class="relative">
                <!-- Vertical line -->
                <div class="absolute left-6 sm:left-8 top-0 bottom-0 w-0.5 bg-gold/20"></div>

                <!-- Stage 1 — COMPLETED -->
                <div class="relative flex items-start mb-12 group">
                    <!-- Dot -->
                    <div class="relative z-10 flex-shrink-0 w-12 h-12 sm:w-16 sm:h-16 gradient-gold rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-check text-white text-lg sm:text-xl"></i>
                    </div>
                    <!-- Content -->
                    <div class="ml-6 flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span class="text-xs font-semibold text-gold uppercase tracking-wider">Этап 1</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Завершён
                            </span>
                        </div>
                        <a href="{{ route('development-plan.stage', 'stage-1') }}" class="block bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift group-hover:border-gold/30 transition-colors">
                            <h3 class="font-serif text-xl sm:text-2xl font-bold text-dark mb-2 group-hover:text-primary transition-colors">
                                Инфраструктура и база данных
                                <i class="fas fa-arrow-right text-gold text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </h3>
                            <p class="text-warm-gray text-sm mb-4">
                                Фундамент платформы: база данных, авторизация, роли пользователей, политики доступа, модели данных и базовые панели управления.
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">10 таблиц</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">7 моделей</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">3 роли</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">4 политики</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">30 000 ₽</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Stage 2 — IN PROGRESS -->
                <div class="relative flex items-start mb-12 group">
                    <!-- Dot -->
                    <div class="relative z-10 flex-shrink-0 w-12 h-12 sm:w-16 sm:h-16 bg-white border-4 border-gold rounded-full flex items-center justify-center shadow-md">
                        <i class="fas fa-spinner fa-spin text-gold text-lg sm:text-xl"></i>
                    </div>
                    <!-- Content -->
                    <div class="ml-6 flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span class="text-xs font-semibold text-gold uppercase tracking-wider">Этап 2</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> В работе
                            </span>
                        </div>
                        <a href="{{ route('development-plan.stage', 'stage-2') }}" class="block bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift group-hover:border-gold/30 transition-colors">
                            <h3 class="font-serif text-xl sm:text-2xl font-bold text-dark mb-2 group-hover:text-primary transition-colors">
                                Панели управления, профили и организации
                                <i class="fas fa-arrow-right text-gold text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </h3>
                            <p class="text-warm-gray text-sm mb-4">
                                Полноценные панели для каждой роли, управление участниками, CRUD организаций с верификацией, административные панели.
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Дашборды</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Профили</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Организации</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Верификация</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">30 000 ₽</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Stage 3 — PLANNED -->
                <div class="relative flex items-start mb-12 group">
                    <!-- Dot -->
                    <div class="relative z-10 flex-shrink-0 w-12 h-12 sm:w-16 sm:h-16 bg-white border-4 border-warm-gray/30 rounded-full flex items-center justify-center">
                        <span class="font-serif font-bold text-warm-gray text-lg">3</span>
                    </div>
                    <!-- Content -->
                    <div class="ml-6 flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span class="text-xs font-semibold text-warm-gray uppercase tracking-wider">Этап 3</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                <i class="fas fa-hourglass-half mr-1"></i> Запланировано
                            </span>
                        </div>
                        <a href="{{ route('development-plan.stage', 'stage-3') }}" class="block bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover-lift group-hover:border-gold/30 transition-colors">
                            <h3 class="font-serif text-xl sm:text-2xl font-bold text-dark mb-2 group-hover:text-primary transition-colors">
                                Конкурсы и подача заявок
                                <i class="fas fa-arrow-right text-warm-gray text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </h3>
                            <p class="text-warm-gray text-sm mb-4">
                                Полный цикл конкурсов: создание, публикация, подача заявок с загрузкой файлов, автоматический переход статусов.
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Конкурсы</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Заявки</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Загрузка файлов</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Автопереходы</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">30 000 ₽</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Stage 4 — PLANNED -->
                <div class="relative flex items-start group">
                    <!-- Dot -->
                    <div class="relative z-10 flex-shrink-0 w-12 h-12 sm:w-16 sm:h-16 bg-white border-4 border-warm-gray/30 rounded-full flex items-center justify-center">
                        <span class="font-serif font-bold text-warm-gray text-lg">4</span>
                    </div>
                    <!-- Content -->
                    <div class="ml-6 flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span class="text-xs font-semibold text-warm-gray uppercase tracking-wider">Этап 4</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                <i class="fas fa-hourglass-half mr-1"></i> Запланировано
                            </span>
                        </div>
                        <a href="{{ route('development-plan.stage', 'stage-4') }}" class="block bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover-lift group-hover:border-gold/30 transition-colors">
                            <h3 class="font-serif text-xl sm:text-2xl font-bold text-dark mb-2 group-hover:text-primary transition-colors">
                                Оценка, дипломы и уведомления
                                <i class="fas fa-arrow-right text-warm-gray text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                            </h3>
                            <p class="text-warm-gray text-sm mb-4">
                                Интерфейс жюри, автоматическая генерация PDF-дипломов, email-уведомления и финальное тестирование.
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Оценка</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Дипломы PDF</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Уведомления</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Тестирование</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">30 000 ₽</span>
                            </div>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ========== PROGRESS BAR ========== -->
    <section class="py-12 sm:py-16 px-4 bg-cream-dark">
        <div class="max-w-3xl mx-auto text-center">
            <h3 class="font-serif text-xl sm:text-2xl font-bold text-dark mb-6">Общий прогресс</h3>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10">
                <div class="flex justify-between text-sm text-warm-gray mb-2">
                    <span>Выполнено этапов: 1 из 4</span>
                    <span class="font-semibold text-primary">25%</span>
                </div>
                <div class="w-full bg-cream-dark rounded-full h-4 overflow-hidden">
                    <div class="gradient-gold h-4 rounded-full transition-all duration-1000" style="width: 25%"></div>
                </div>

                <div class="grid grid-cols-4 gap-2 mt-4 text-xs text-warm-gray">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-green-500 mb-1"></i>
                        <div>Этап 1</div>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-gold mb-1"></i>
                        <div>Этап 2</div>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-circle text-gray-300 mb-1"></i>
                        <div>Этап 3</div>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-circle text-gray-300 mb-1"></i>
                        <div>Этап 4</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer class="bg-dark text-cream py-12 px-4">
        <div class="max-w-6xl mx-auto">
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
                    <p class="text-warm-gray text-sm">
                        Всероссийская платформа для проведения онлайн-конкурсов, оценки работ и выдачи дипломов.
                    </p>
                </div>

                <div>
                    <h5 class="font-serif font-semibold text-gold mb-4">Разделы</h5>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="text-warm-gray hover:text-gold transition-colors">Главная</a></li>
                        <li><a href="#" class="text-warm-gray hover:text-gold transition-colors">Конкурсы</a></li>
                        <li><a href="{{ route('development-plan') }}" class="text-warm-gray hover:text-gold transition-colors">План развития</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="font-serif font-semibold text-gold mb-4">Организаторам</h5>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-warm-gray hover:text-gold transition-colors">Создать организацию</a></li>
                        <li><a href="#" class="text-warm-gray hover:text-gold transition-colors">Провести конкурс</a></li>
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
