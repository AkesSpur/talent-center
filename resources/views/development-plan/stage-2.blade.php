@extends('layouts.public')

@section('title', 'Этап 2 — Панели управления, профили и организации — ' . config('app.name', 'Талант-центр'))

@section('content')

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-16 sm:py-24 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <a href="{{ route('development-plan') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Вернуться к плану развития
            </a>

            <div class="inline-flex items-center px-4 py-2 rounded-full bg-green-50 border border-green-200 text-sm text-green-700 font-medium mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                Этап 2 завершён
            </div>

            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-dark mb-6">
                Панели управления, профили и организации
            </h2>

            <p class="text-lg text-warm-gray max-w-2xl mx-auto mb-8">
                Второй этап включает создание полноценных панелей управления для каждой роли, систему управления участниками, полный CRUD организаций и процесс верификации.
            </p>

        </div>
    </section>

    <!-- ========== PLANNED DELIVERABLES ========== -->
    <section class="py-16 sm:py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Запланированные работы</h3>
                <p class="text-warm-gray max-w-xl mx-auto">Подробный список всех компонентов, которые будут реализованы на этом этапе.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-tachometer-alt text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Дашборд участника</h4>
                    <p class="text-warm-gray text-sm">
                        Полноценная панель участника: персональная информация, список детей-участников, организации, заявки и дипломы.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-child text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Управление участниками</h4>
                    <p class="text-warm-gray text-sm">
                        Родитель может создавать и редактировать профили детей-участников, подавать заявки от их имени.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-building text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">CRUD организаций</h4>
                    <p class="text-warm-gray text-sm">
                        Создание, просмотр, редактирование организаций. Управление представителями с гранулярными правами.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-check-double text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Верификация организаций</h4>
                    <p class="text-warm-gray text-sm">
                        Процесс проверки организаций администратором и модератором. Только верифицированные организации могут создавать конкурсы.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-crown text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Админ-панель</h4>
                    <p class="text-warm-gray text-sm">
                        Статистика платформы, управление пользователями, модерация организаций, просмотр журнала действий.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gold/10 hover-lift">
                    <div class="w-12 h-12 gradient-gold rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-headset text-white text-lg"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Панель поддержки</h4>
                    <p class="text-warm-gray text-sm">
                        Модерация пользователей, проверка организаций, просмотр заявок. Ограниченные права без удаления.
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
                <p class="text-warm-gray max-w-xl mx-auto">Детальный список задач, которые будут выполнены.</p>
            </div>

            <div class="bg-white rounded-xl p-6 sm:p-8 shadow-sm border border-gold/10">
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Полноценный дашборд участника с карточками информации</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Управление детьми-участниками (создание, редактирование)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">CRUD организаций с управлением представителями</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Верификация организаций (админ и поддержка)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Полная админ-панель со статистикой</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Управление пользователями (админ): список, поиск, блокировка</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Панель поддержки с модерацией</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Логирование действий через ActionLogService</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ========== NAVIGATION ========== -->
    <section class="py-12 px-4">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <a href="{{ route('development-plan.stage', 'stage-1') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Этап 1: Инфраструктура
            </a>
            <a href="{{ route('development-plan.stage', 'stage-3') }}" class="inline-flex items-center px-6 py-3 gradient-gold text-dark font-semibold rounded-lg text-sm hover:opacity-90 transition">
                Этап 3: Конкурсы и заявки <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </section>

@endsection
