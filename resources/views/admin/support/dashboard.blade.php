<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <x-breadcrumbs :items="[
                    ['label' => 'Поддержка', 'url' => route('admin.support.tickets.index')],
                    ['label' => 'Аналитика'],
                ]" />
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Аналитика поддержки</h2>
                <p class="text-warm-gray mt-1">Нагрузка и скорость работы за последние 30 дней</p>
            </div>
            <a href="{{ route('admin.support.tickets.index') }}"
                class="self-start inline-flex items-center px-4 py-2.5 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm">
                <i class="fas fa-inbox mr-2" aria-hidden="true"></i>К заявкам
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="supportAnalytics(@js($series), @js($statuses))">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            {{-- Tiles --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-5">
                    <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">В работе</p>
                    <p class="font-serif text-2xl font-bold text-dark">{{ $tiles['inProgress'] }}</p>
                </div>
                <a href="{{ route('admin.support.tickets.index', ['overdue' => 1]) }}"
                    class="bg-white rounded-xl shadow-sm border {{ $tiles['overdue'] > 0 ? 'border-red-200' : 'border-gold/10' }} p-5 hover:border-primary/30 transition-colors">
                    <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">Просроченные</p>
                    <p class="font-serif text-2xl font-bold {{ $tiles['overdue'] > 0 ? 'text-red-600' : 'text-dark' }}">{{ $tiles['overdue'] }}</p>
                </a>
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-5">
                    <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">Решено за сутки</p>
                    <p class="font-serif text-2xl font-bold text-dark">{{ $tiles['resolved24'] }}</p>
                </div>
            </div>

            {{-- New vs closed --}}
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                <h3 class="font-serif text-lg font-semibold text-dark mb-4">Новые и закрытые заявки</h3>
                <div class="h-72">
                    <canvas x-ref="flow" aria-label="График новых и закрытых заявок по дням"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- By status --}}
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                    <h3 class="font-serif text-lg font-semibold text-dark mb-4">Текущие заявки по статусам</h3>
                    <div class="h-64">
                        <canvas x-ref="statuses" aria-label="Диаграмма заявок по статусам"></canvas>
                    </div>
                    <dl class="mt-4 space-y-1 text-sm">
                        @foreach($statuses as $status)
                            <div class="flex items-center justify-between">
                                <dt class="text-warm-gray">{{ $status['label'] }}</dt>
                                <dd class="font-medium text-dark">{{ $status['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- Resolution time --}}
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                    <h3 class="font-serif text-lg font-semibold text-dark mb-4">Среднее время решения, часов</h3>
                    <div class="h-64">
                        <canvas x-ref="resolution" aria-label="График среднего времени решения по дням"></canvas>
                    </div>
                    <p class="mt-4 text-xs text-warm-gray">
                        Считается от создания заявки до её закрытия. Дни без закрытых заявок в графике пропускаются.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
