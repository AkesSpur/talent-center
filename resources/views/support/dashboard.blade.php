<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif font-semibold text-xl text-dark leading-tight">
            Панель поддержки
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-message />

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-clock mr-1"></i> Организации на проверке</div>
                    <div class="mt-1 text-3xl font-semibold text-orange-600">{{ $pendingOrgsCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-users mr-1"></i> Всего пользователей</div>
                    <div class="mt-1 text-3xl font-semibold text-dark">{{ $usersCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-file-alt mr-1"></i> Заявки</div>
                    <div class="mt-1 text-3xl font-semibold text-dark">{{ $applicationsCount }}</div>
                </div>
            </div>

            <!-- Быстрые ссылки -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('support.users.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gold/10 hover:bg-cream/50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="font-medium text-dark">Управление пользователями</div>
                            <div class="text-sm text-warm-gray">Поиск, блокировка</div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('support.organizations.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gold/10 hover:bg-cream/50 transition group">
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
            </div>

            <!-- Организации на проверке -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-dark">
                            <i class="fas fa-building-circle-exclamation text-gold mr-2"></i> Ожидают верификации
                        </h3>
                        <a href="{{ route('support.organizations.index', ['status' => 'pending']) }}" class="text-sm text-primary hover:text-primary/80">
                            Все организации <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    @if($pendingOrgs->count())
                        <div class="space-y-3">
                            @foreach($pendingOrgs as $org)
                                <a href="{{ route('support.organizations.show', $org) }}" class="block p-3 rounded-lg border border-primary/10 hover:bg-cream/50 transition">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-medium text-dark">{{ $org->name }}</div>
                                            <div class="text-sm text-warm-gray">
                                                ИНН: {{ $org->inn }}
                                                @if($org->createdBy)
                                                    &middot; {{ $org->createdBy->last_name }} {{ $org->createdBy->first_name }}
                                                @endif
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> На проверке
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-warm-gray text-sm">Нет организаций, ожидающих верификации.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
