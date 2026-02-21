<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Этап 4 — Оценка, дипломы и уведомления — {{ config('app.name', 'Талант-центр') }}</title>

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
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-12 h-12 gradient-gold rounded-full flex items-center justify-center shadow-sm">
                        <i class="fas fa-award text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="font-serif text-xl font-bold text-primary">Талант-центр</h1>
                        <p class="text-xs text-warm-gray">Всероссийский центр талантов</p>
                    </div>
                </a>

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
    <section class="pattern-bg py-16 sm:py-24 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <a href="{{ route('development-plan') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Вернуться к плану развития
            </a>

            <div class="inline-flex items-center px-4 py-2 rounded-full bg-gray-100 border border-gray-200 text-sm text-gray-600 font-medium mb-6">
                <i class="fas fa-hourglass-half mr-2"></i>
                Этап 4 — Запланировано
            </div>

            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-dark mb-6">
                Оценка, дипломы и уведомления
            </h2>

            <p class="text-lg text-warm-gray max-w-2xl mx-auto mb-8">
                Финальный этап: интерфейс жюри для оценки работ, автоматическая генерация PDF-дипломов, система email-уведомлений и полное тестирование платформы.
            </p>

            <div class="inline-flex items-center px-4 py-2 rounded-full bg-gold/10 border border-gold/30 text-sm text-primary font-medium">
                <i class="fas fa-ruble-sign mr-2 text-gold"></i>
                Бюджет этапа: 30 000 ₽
            </div>
        </div>
    </section>

    <!-- ========== PLANNED DELIVERABLES ========== -->
    <section class="py-16 sm:py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Запланированные работы</h3>
                <p class="text-warm-gray max-w-xl mx-auto">Все компоненты финального этапа разработки.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-gavel text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Интерфейс жюри</h4>
                    <p class="text-warm-gray text-sm">
                        Панель оценки заявок: список конкурсов на оценке, группировка по категориям, присвоение мест (1-е, 2-е, 3-е, участник, отклонено).
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-lock text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Финализация оценки</h4>
                    <p class="text-warm-gray text-sm">
                        Кнопка «Завершить оценку» доступна только когда все заявки оценены. После финализации конкурс переходит в архив.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-certificate text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Генерация дипломов</h4>
                    <p class="text-warm-gray text-sm">
                        Автоматическое создание PDF-дипломов при присвоении места. Настраиваемый фон, данные участника, конкурса и организации.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-envelope text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Email-уведомления</h4>
                    <p class="text-warm-gray text-sm">
                        Уведомления о подаче заявки, смене статуса конкурса, присвоении места и отклонении. С возможностью отписки.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-tools text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Инструменты админа</h4>
                    <p class="text-warm-gray text-sm">
                        Ручная корректировка оценок, управление шаблонами дипломов, повторная генерация дипломов для конкурса.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-vial text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Финальное тестирование</h4>
                    <p class="text-warm-gray text-sm">
                        Полное тестирование всех потоков: регистрация, создание конкурса, подача заявки, оценка, дипломы. Проверка на мобильных устройствах.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== TASK LIST ========== -->
    <section class="py-16 sm:py-20 px-4 bg-cream-dark">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Задачи этапа</h3>
                <p class="text-warm-gray max-w-xl mx-auto">Все задачи финального этапа разработки.</p>
            </div>

            <div class="bg-white rounded-xl p-6 sm:p-8 shadow-sm border border-gold/10">
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Дашборд жюри: список конкурсов на оценке</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Страница оценки заявок с группировкой по категориям</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Присвоение мест с цветовой индикацией</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Кнопка «Завершить оценку» с проверкой полноты</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Генерация PDF-дипломов (barryvdh/laravel-dompdf)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Страница «Мои дипломы» с возможностью скачивания</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Email-уведомления (Laravel Mailables + очереди)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Возможность отписки от email-уведомлений</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Админ-инструменты для управления оценками и дипломами</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Полное end-to-end тестирование всех потоков</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-300 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Адаптивное тестирование на мобильных устройствах</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ========== NAVIGATION ========== -->
    <section class="py-12 px-4">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <a href="{{ route('development-plan.stage', 'stage-3') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Этап 3: Конкурсы и заявки
            </a>
            <a href="{{ route('development-plan') }}" class="inline-flex items-center px-6 py-3 gradient-gold text-dark font-semibold rounded-lg text-sm hover:opacity-90 transition">
                <i class="fas fa-list mr-2"></i> К плану развития
            </a>
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
