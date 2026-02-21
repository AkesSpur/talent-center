<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Этап 1 — Инфраструктура и база данных — {{ config('app.name', 'Талант-центр') }}</title>

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

    <!-- ========== BREADCRUMB & HERO ========== -->
    <section class="pattern-bg py-16 sm:py-24 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <a href="{{ route('development-plan') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Вернуться к плану развития
            </a>

            <div class="inline-flex items-center px-4 py-2 rounded-full bg-green-50 border border-green-200 text-sm text-green-700 font-medium mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                Этап 1 завершён
            </div>

            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-dark mb-6">
                Инфраструктура и база данных
            </h2>

            <p class="text-lg text-warm-gray max-w-2xl mx-auto mb-12">
                Мы завершили первый этап разработки платформы «Талант-центр». Ниже — обзор того, что уже создано: архитектура базы данных, система авторизации, роли, политики доступа и многое другое.
            </p>

            <!-- Stats row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gold/10 hover-lift">
                    <div class="text-3xl font-bold text-primary">10</div>
                    <div class="text-sm text-warm-gray mt-1">таблиц в базе</div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gold/10 hover-lift">
                    <div class="text-3xl font-bold text-primary">7</div>
                    <div class="text-sm text-warm-gray mt-1">моделей данных</div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gold/10 hover-lift">
                    <div class="text-3xl font-bold text-primary">3</div>
                    <div class="text-sm text-warm-gray mt-1">роли пользователей</div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gold/10 hover-lift">
                    <div class="text-3xl font-bold text-primary">4</div>
                    <div class="text-sm text-warm-gray mt-1">политики доступа</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== DELIVERABLES ========== -->
    <section class="py-16 sm:py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Что было сделано</h3>
                <p class="text-warm-gray max-w-xl mx-auto">Каждый блок ниже — это часть технического фундамента, на котором строится вся платформа.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-database text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">База данных</h4>
                    <p class="text-warm-gray text-sm">
                        Спроектировано и развёрнуто 10 таблиц: пользователи, организации, конкурсы, заявки, дипломы, категории и журнал действий. Все связи между таблицами настроены с каскадным удалением и индексацией.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-lock text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Система входа</h4>
                    <p class="text-warm-gray text-sm">
                        Полноценная авторизация: регистрация, вход, подтверждение email, сброс пароля. Пользователи могут указать фамилию, имя и отчество при регистрации.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-users-cog text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Роли пользователей</h4>
                    <p class="text-warm-gray text-sm">
                        Три роли: администратор, модератор и участник. Каждая роль видит свою панель управления и имеет свой набор прав. Middleware проверяет роль на каждом запросе.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-building text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Организации</h4>
                    <p class="text-warm-gray text-sm">
                        Структура для организаций с гибкой системой прав представителей. Каждому представителю можно выдать отдельные разрешения: создание конкурсов, управление и оценка заявок.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-shield-alt text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Защита и политики</h4>
                    <p class="text-warm-gray text-sm">
                        Четыре политики авторизации для ключевых моделей. Три middleware для проверки ролей и прав. Организация должна пройти верификацию перед созданием конкурсов.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-cogs text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Инфраструктура</h4>
                    <p class="text-warm-gray text-sm">
                        Сервис логирования действий, сидеры тестовых данных, пять PHP-перечислений для статусов. Панели управления для каждой роли с базовой статистикой.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== DATABASE ========== -->
    <section class="py-16 sm:py-20 px-4 bg-cream-dark">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Архитектура базы данных</h3>
                <p class="text-warm-gray max-w-xl mx-auto">10 таблиц спроектированы с учётом всех этапов проекта — от регистрации до выдачи дипломов.</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-dark text-cream text-left">
                            <th class="px-6 py-3 text-sm font-semibold">Таблица</th>
                            <th class="px-6 py-3 text-sm font-semibold">Назначение</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gold/10">
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-user text-gold mr-2"></i>users</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Все пользователи платформы с ролями и связью родитель-ребёнок</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-building text-gold mr-2"></i>organizations</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Организации-организаторы конкурсов со статусом верификации</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-link text-gold mr-2"></i>organization_user</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Связь пользователей с организациями и гранулярные права</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-trophy text-gold mr-2"></i>contests</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Конкурсы с датами, статусами и фоном для дипломов</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-list text-gold mr-2"></i>contest_categories</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Категории и номинации внутри каждого конкурса</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-file-alt text-gold mr-2"></i>applications</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Заявки участников с файлами, статусами и оценками</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-certificate text-gold mr-2"></i>diplomas</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Сгенерированные дипломы в формате PDF</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-history text-gold mr-2"></i>action_logs</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Журнал действий с IP-адресами и метаданными</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-desktop text-gold mr-2"></i>sessions</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Активные сессии пользователей</td>
                        </tr>
                        <tr class="hover:bg-cream transition-colors">
                            <td class="px-6 py-3 text-sm font-medium"><i class="fas fa-key text-gold mr-2"></i>password_reset_tokens</td>
                            <td class="px-6 py-3 text-sm text-warm-gray">Токены для безопасного сброса паролей</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ========== ROLES ========== -->
    <section class="py-16 sm:py-20 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Роли и права доступа</h3>
                <p class="text-warm-gray max-w-xl mx-auto">Каждая роль видит свою панель управления и имеет чётко определённые возможности.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-14 h-14 gradient-gold rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-crown text-white text-xl"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark text-center mb-3">Администратор</h4>
                    <ul class="text-sm text-warm-gray space-y-2">
                        <li><i class="fas fa-check text-gold mr-2"></i>Полный доступ ко всем разделам</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Управление пользователями</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Верификация организаций</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Просмотр журнала действий</li>
                    </ul>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-headset text-primary text-xl"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark text-center mb-3">Модератор</h4>
                    <ul class="text-sm text-warm-gray space-y-2">
                        <li><i class="fas fa-check text-gold mr-2"></i>Модерация пользователей</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Проверка организаций</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Просмотр заявок</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Без доступа к удалению</li>
                    </ul>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-14 h-14 bg-cream-dark rounded-full flex items-center justify-center mb-4 mx-auto border border-gold/20">
                        <i class="fas fa-user text-warm-gray text-xl"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark text-center mb-3">Участник</h4>
                    <ul class="text-sm text-warm-gray space-y-2">
                        <li><i class="fas fa-check text-gold mr-2"></i>Подача заявок на конкурсы</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Управление детьми-участниками</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Представительство в организациях</li>
                        <li><i class="fas fa-check text-gold mr-2"></i>Скачивание дипломов</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== CHECKLIST ========== -->
    <section class="py-16 sm:py-20 px-4 bg-cream-dark">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Чек-лист выполнения</h3>
                <p class="text-warm-gray max-w-xl mx-auto">Полный список задач первого этапа с отметками о выполнении.</p>
            </div>

            <div class="bg-white rounded-xl p-6 sm:p-8 shadow-sm border border-gold/10">
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Laravel 12 проект инициализирован с MySQL</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">10 миграций базы данных</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">7 Eloquent-моделей с полными отношениями и кастами</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">5 PHP-перечислений (UserRole, OrganizationStatus, ContestStatus, ApplicationStatus, FileType)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">3 кастомных middleware (CheckRole, CheckOrgPermission, EnsureOrgVerified)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">4 политики авторизации (User, Organization, Contest, Application)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Авторизация через Breeze: регистрация с ФИО, вход, подтверждение email, сброс пароля</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Редактирование профиля (имя, фамилия, отчество, телефон, email)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">3 панели управления (админ, поддержка, участник)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Навигация с учётом ролей и цветными бейджами</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Компонент flash-сообщений</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">ActionLogService — сервис логирования действий</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Сидеры тестовых данных (AdminSeeder + TestDataSeeder)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Связь хранилища (storage:link)</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ========== NAVIGATION ========== -->
    <section class="py-12 px-4">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <a href="{{ route('development-plan') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> План развития
            </a>
            <a href="{{ route('development-plan.stage', 'stage-2') }}" class="inline-flex items-center px-6 py-3 gradient-gold text-dark font-semibold rounded-lg text-sm hover:opacity-90 transition">
                Этап 2: Панели и организации <i class="fas fa-arrow-right ml-2"></i>
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
