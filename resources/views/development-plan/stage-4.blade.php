@extends('layouts.public')

@section('title', 'Этап 4 — Оценка, дипломы и уведомления — ' . config('app.name', 'Талант-центр'))

@section('content')

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-16 sm:py-24 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <a href="{{ route('development-plan') }}" class="inline-flex items-center text-sm text-warm-gray hover:text-primary transition-colors mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Вернуться к плану развития
            </a>

            <div class="inline-flex items-center px-4 py-2 rounded-full bg-green-50 border border-green-200 text-sm text-green-700 font-medium mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                Этап 4 — Завершён
            </div>

            <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-dark mb-6">
                Оценка, дипломы и уведомления
            </h2>

            <p class="text-lg text-warm-gray max-w-2xl mx-auto mb-8">
                Финальный этап: интерфейс жюри для оценки работ, автоматическая генерация PDF-дипломов, система email-уведомлений и полное тестирование платформы.
            </p>

        </div>
    </section>

    <!-- ========== PLANNED DELIVERABLES ========== -->
    <section class="py-16 sm:py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h3 class="font-serif text-2xl sm:text-3xl font-bold text-dark mb-4">Работы этапа</h3>
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
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Дашборд жюри: список конкурсов на оценке</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Страница оценки заявок с группировкой по категориям</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Присвоение мест с цветовой индикацией</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Кнопка «Завершить оценку» с проверкой полноты</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Генерация PDF-дипломов (barryvdh/laravel-dompdf)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Страница «Мои дипломы» с возможностью скачивания</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Email-уведомления (Laravel Mailables + очереди)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
                        <span class="text-sm text-dark">Возможность отписки от email-уведомлений</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
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

@endsection
