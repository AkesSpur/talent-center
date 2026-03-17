@extends('layouts.public')

@section('title', 'План развития — ' . config('app.name', 'Талант-центр'))

@section('content')

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-10 sm:py-16 md:py-20 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors mb-4 sm:mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Вернуться на главную
            </a>

            <h2 class="font-serif text-2xl sm:text-4xl md:text-5xl font-bold text-dark mb-4 sm:mb-6">
                План развития
            </h2>

            <p class="text-base sm:text-lg text-warm-gray max-w-2xl mx-auto mb-4">
                Платформа «Талант-центр» создаётся в 4 этапа. Здесь вы можете отслеживать прогресс разработки и ознакомиться с подробным отчётом по каждому завершённому этапу.
            </p>

        </div>
    </section>

    <!-- ========== TIMELINE ========== -->
    <section class="py-10 sm:py-16 md:py-20 px-4">
        <div class="max-w-3xl mx-auto">

            <!-- Timeline -->
            <div class="relative">
                <!-- Vertical line -->
                <div class="absolute left-5 sm:left-8 top-0 bottom-0 w-0.5 bg-gold/20"></div>

                <!-- Stage 1 — COMPLETED -->
                <div class="relative flex items-start mb-8 sm:mb-12 group">
                    <!-- Dot -->
                    <div class="relative z-10 flex-shrink-0 w-10 h-10 sm:w-16 sm:h-16 gradient-gold rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-check text-white text-sm sm:text-xl"></i>
                    </div>
                    <!-- Content -->
                    <div class="ml-4 sm:ml-6 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                            <span class="text-xs font-semibold text-gold uppercase tracking-wider">Этап 1</span>
                            <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Завершён
                            </span>
                        </div>
                        <a href="{{ route('development-plan.stage', 'stage-1') }}" class="block bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gold/10 hover-lift group-hover:border-gold/30 transition-colors">
                            <h3 class="font-serif text-base sm:text-xl md:text-2xl font-bold text-dark mb-2 group-hover:text-primary transition-colors">
                                Инфраструктура и база данных
                                <i class="fas fa-arrow-right text-gold text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity hidden sm:inline"></i>
                            </h3>
                            <p class="text-warm-gray text-xs sm:text-sm mb-3 sm:mb-4">
                                Фундамент платформы: база данных, авторизация, роли пользователей, политики доступа, модели данных и базовые панели управления.
                            </p>
                            <div class="flex flex-wrap gap-1.5 sm:gap-2 text-xs">
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">10 таблиц</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">7 моделей</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">3 роли</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray hidden sm:inline-block">4 политики</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Stage 2 — COMPLETED -->
                <div class="relative flex items-start mb-8 sm:mb-12 group">
                    <!-- Dot -->
                    <div class="relative z-10 flex-shrink-0 w-10 h-10 sm:w-16 sm:h-16 gradient-gold rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-check text-white text-sm sm:text-xl"></i>
                    </div>
                    <!-- Content -->
                    <div class="ml-4 sm:ml-6 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                            <span class="text-xs font-semibold text-gold uppercase tracking-wider">Этап 2</span>
                            <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Завершён
                            </span>
                        </div>
                        <a href="{{ route('development-plan.stage', 'stage-2') }}" class="block bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gold/10 hover-lift group-hover:border-gold/30 transition-colors">
                            <h3 class="font-serif text-base sm:text-xl md:text-2xl font-bold text-dark mb-2 group-hover:text-primary transition-colors">
                                Панели управления, профили и организации
                                <i class="fas fa-arrow-right text-gold text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity hidden sm:inline"></i>
                            </h3>
                            <p class="text-warm-gray text-xs sm:text-sm mb-3 sm:mb-4">
                                Полноценные панели для каждой роли, управление участниками, CRUD организаций с верификацией, административные панели.
                            </p>
                            <div class="flex flex-wrap gap-1.5 sm:gap-2 text-xs">
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Дашборды</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Профили</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Организации</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray hidden sm:inline-block">Верификация</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Stage 3 — COMPLETED -->
                <div class="relative flex items-start mb-8 sm:mb-12 group">
                    <!-- Dot -->
                    <div class="relative z-10 flex-shrink-0 w-10 h-10 sm:w-16 sm:h-16 gradient-gold rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-check text-white text-sm sm:text-xl"></i>
                    </div>
                    <!-- Content -->
                    <div class="ml-4 sm:ml-6 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                            <span class="text-xs font-semibold text-gold uppercase tracking-wider">Этап 3</span>
                            <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Завершён
                            </span>
                        </div>
                        <a href="{{ route('development-plan.stage', 'stage-3') }}" class="block bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gold/10 hover-lift group-hover:border-gold/30 transition-colors">
                            <h3 class="font-serif text-base sm:text-xl md:text-2xl font-bold text-dark mb-2 group-hover:text-primary transition-colors">
                                Конкурсы и подача заявок
                                <i class="fas fa-arrow-right text-warm-gray text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity hidden sm:inline"></i>
                            </h3>
                            <p class="text-warm-gray text-xs sm:text-sm mb-3 sm:mb-4">
                                Полный цикл конкурсов: создание, публикация, подача заявок с загрузкой файлов, автоматический переход статусов.
                            </p>
                            <div class="flex flex-wrap gap-1.5 sm:gap-2 text-xs">
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Конкурсы</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Заявки</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray hidden sm:inline-block">Загрузка файлов</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray hidden sm:inline-block">Автопереходы</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Stage 4 — LARGELY COMPLETE -->
                <div class="relative flex items-start group">
                    <!-- Dot -->
                    <div class="relative z-10 flex-shrink-0 w-10 h-10 sm:w-16 sm:h-16 gradient-gold rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-check text-white text-sm sm:text-xl"></i>
                    </div>
                    <!-- Content -->
                    <div class="ml-4 sm:ml-6 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                            <span class="text-xs font-semibold text-gold uppercase tracking-wider">Этап 4</span>
                            <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Почти завершён
                            </span>
                        </div>
                        <a href="{{ route('development-plan.stage', 'stage-4') }}" class="block bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gray-200 hover-lift group-hover:border-gold/30 transition-colors">
                            <h3 class="font-serif text-base sm:text-xl md:text-2xl font-bold text-dark mb-2 group-hover:text-primary transition-colors">
                                Оценка, дипломы и уведомления
                                <i class="fas fa-arrow-right text-warm-gray text-sm ml-2 opacity-0 group-hover:opacity-100 transition-opacity hidden sm:inline"></i>
                            </h3>
                            <p class="text-warm-gray text-xs sm:text-sm mb-3 sm:mb-4">
                                Интерфейс жюри, автоматическая генерация PDF-дипломов, email-уведомления и финальное тестирование.
                            </p>
                            <div class="flex flex-wrap gap-1.5 sm:gap-2 text-xs">
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Оценка</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray">Дипломы PDF</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray hidden sm:inline-block">Уведомления</span>
                                <span class="px-2 py-1 bg-cream-dark rounded-md text-warm-gray hidden sm:inline-block">Тестирование</span>
                            </div>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ========== PROGRESS BAR ========== -->
    <section class="py-10 sm:py-16 px-4 bg-cream-dark">
        <div class="max-w-3xl mx-auto text-center">
            <h3 class="font-serif text-lg sm:text-2xl font-bold text-dark mb-4 sm:mb-6">Общий прогресс</h3>

            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-gold/10">
                <div class="flex justify-between text-xs sm:text-sm text-warm-gray mb-2">
                    <span>Финальное тестирование: этап 4 в завершающей стадии</span>
                    <span class="font-semibold text-primary">90%</span>
                </div>
                <div class="w-full bg-cream-dark rounded-full h-3 sm:h-4 overflow-hidden">
                    <div class="gradient-gold h-3 sm:h-4 rounded-full transition-all duration-1000" style="width: 90%"></div>
                </div>

                <div class="grid grid-cols-4 gap-1 sm:gap-2 mt-3 sm:mt-4 text-xs text-warm-gray">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-green-500 mb-1"></i>
                        <div>Этап 1</div>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-check-circle text-green-500 mb-1"></i>
                        <div>Этап 2</div>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-check-circle text-green-500 mb-1"></i>
                        <div>Этап 3</div>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-gold mb-1"></i>
                        <div>Этап 4</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
