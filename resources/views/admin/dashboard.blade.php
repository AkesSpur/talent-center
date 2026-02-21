<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark leading-tight">
            Панель администратора
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash-message />

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-users mr-1"></i> Пользователи</div>
                    <div class="mt-1 text-3xl font-semibold text-dark">{{ $usersCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-building mr-1"></i> Организации</div>
                    <div class="mt-1 text-3xl font-semibold text-dark">{{ $organizationsCount }}</div>
                    <div class="mt-1 text-xs text-warm-gray">
                        <span class="text-green-600">{{ $verifiedOrgsCount }} верифиц.</span> /
                        <span class="text-orange-600">{{ $pendingOrgsCount }} на проверке</span>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-trophy mr-1"></i> Конкурсы</div>
                    <div class="mt-1 text-3xl font-semibold text-dark">{{ $contestsCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-file-alt mr-1"></i> Заявки</div>
                    <div class="mt-1 text-3xl font-semibold text-dark">{{ $applicationsCount }}</div>
                </div>
            </div>

            <!-- Быстрые ссылки -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ route('admin.users.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gold/10 hover:bg-cream/50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="font-medium text-dark">Управление пользователями</div>
                            <div class="text-sm text-warm-gray">Поиск, роли, блокировка</div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.organizations.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gold/10 hover:bg-cream/50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div class="font-medium text-dark">Управление организациями</div>
                            <div class="text-sm text-warm-gray">Верификация, просмотр</div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.action-logs.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gold/10 hover:bg-cream/50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition">
                            <i class="fas fa-list-alt"></i>
                        </div>
                        <div>
                            <div class="font-medium text-dark">Журнал действий</div>
                            <div class="text-sm text-warm-gray">Аудит всех изменений</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Последние действия -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-dark">
                            <i class="fas fa-clock-rotate-left text-gold mr-2"></i> Последние действия
                        </h3>
                        <a href="{{ route('admin.action-logs.index') }}" class="text-sm text-primary hover:text-primary/80">
                            Все записи <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    @if($recentLogs->count())
                        <div class="space-y-3">
                            @foreach($recentLogs as $log)
                                <div class="flex items-center gap-3 p-3 rounded-lg border border-primary/5">
                                    <div class="text-xs text-warm-gray whitespace-nowrap">
                                        {{ $log->created_at->format('d.m H:i') }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="font-medium text-dark text-sm">
                                            {{ $log->user?->last_name }} {{ $log->user?->first_name }}
                                        </span>
                                        <span class="text-warm-gray text-sm"> — {{ $log->action }}</span>
                                    </div>
                                    @if($log->target_type)
                                        <div class="text-xs text-warm-gray">
                                            {{ class_basename($log->target_type) }} #{{ $log->target_id }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-warm-gray text-sm">Нет записей.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
