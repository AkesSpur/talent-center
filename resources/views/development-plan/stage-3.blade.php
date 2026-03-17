@extends('layouts.public')

@section('title', 'Этап 3 — Конкурсы и подача заявок — ' . config('app.name', 'Талант-центр'))

@section('content')

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-16 sm:py-24 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <a href="{{ route('development-plan') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Вернуться к плану развития
            </a>

            <div class="inline-flex items-center px-4 py-2 rounded-full bg-green-50 border border-green-200 text-sm text-green-700 font-medium mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                Этап 3 — Завершён
            </div>

            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-dark mb-6">
                Конкурсы и подача заявок
            </h2>

            <p class="text-lg text-warm-gray max-w-2xl mx-auto mb-8">
                Третий этап — полный цикл конкурсов: создание, публикация, подача заявок с загрузкой файлов и автоматический переход статусов. После этого этапа основной конкурсный процесс работает от начала до конца.
            </p>

        </div>
    </section>

    <!-- ========== PLANNED DELIVERABLES ========== -->
    <section class="py-16 sm:py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Запланированные работы</h3>
                <p class="text-warm-gray max-w-xl mx-auto">Все компоненты, которые будут реализованы на этом этапе.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-trophy text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">CRUD конкурсов</h4>
                    <p class="text-warm-gray text-sm">
                        Создание и редактирование конкурсов: название, описание, правила, даты, категории, фон диплома. Доступно только представителям верифицированных организаций.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-tags text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Категории конкурсов</h4>
                    <p class="text-warm-gray text-sm">
                        Динамическое добавление и удаление категорий при создании конкурса. Каждая категория с названием и описанием.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-search text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Каталог конкурсов</h4>
                    <p class="text-warm-gray text-sm">
                        Публичный список конкурсов с фильтрацией по статусу, поиском и карточками конкурсов.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-sync-alt text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Автоматические переходы</h4>
                    <p class="text-warm-gray text-sm">
                        Автоматический перевод конкурса из «Приём заявок» в «Оценка» по истечении срока подачи. Запуск через cron-задачу каждую минуту.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-file-upload text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Подача заявок</h4>
                    <p class="text-warm-gray text-sm">
                        Форма подачи заявки с выбором категории, загрузкой файла (до 4 МБ) или ссылкой на облачное хранилище.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-user-friends text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Заявки за детей</h4>
                    <p class="text-warm-gray text-sm">
                        Родитель может подать заявку от имени любого из своих детей-участников. Выбор «от чьего имени» при подаче.
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
                <p class="text-warm-gray max-w-xl mx-auto">Все задачи третьего этапа.</p>
            </div>

            <div class="bg-white rounded-xl p-6 sm:p-8 shadow-sm border border-gold/10">
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">CRUD конкурсов с валидацией и загрузкой фона диплома</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Управление категориями конкурсов (динамическая форма)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Публичный каталог конкурсов с фильтрацией и поиском</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Детальная страница конкурса</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Автоматический переход статусов (cron-команда)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Форма подачи заявки с загрузкой файлов</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Подача заявки от имени ребёнка-участника</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Просмотр заявок организацией</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Страница «Мои заявки» в личном кабинете</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Form Requests для валидации</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ========== NAVIGATION ========== -->
    <section class="py-12 px-4">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <a href="{{ route('development-plan.stage', 'stage-2') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Этап 2: Панели и организации
            </a>
            <a href="{{ route('development-plan.stage', 'stage-4') }}" class="inline-flex items-center px-6 py-3 gradient-gold text-dark font-semibold rounded-lg text-sm hover:opacity-90 transition">
                Этап 4: Оценка и дипломы <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </section>

@endsection
