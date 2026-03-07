<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark leading-tight">
            Личный кабинет
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Приветствие -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                <div class="p-4 sm:p-6 flex items-center gap-3 sm:gap-4">
                    <x-user-avatar :user="auth()->user()" size="md" class="sm:!w-14 sm:!h-14" />
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base sm:text-lg font-medium text-dark truncate">
                            Добро пожаловать, {{ auth()->user()->first_name }}!
                        </h3>
                        <p class="text-warm-gray text-xs sm:text-sm truncate">
                            {{ auth()->user()->email }}
                            @if(auth()->user()->phone)
                                <span class="hidden sm:inline">&middot; {{ auth()->user()->phone }}</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('profile.edit') }}"
                        class="shrink-0 w-8 h-8 sm:w-auto sm:h-auto flex items-center justify-center sm:inline-flex rounded-lg border border-primary/20 text-primary hover:bg-primary/5 transition-colors sm:px-3 sm:py-2 sm:rounded-lg sm:border sm:text-sm">
                        <i class="fas fa-pen text-xs sm:mr-1.5"></i>
                        <span class="hidden sm:inline">Редактировать профиль</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Мои организации -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-dark">
                                <i class="fas fa-building text-gold mr-2"></i> Мои организации
                            </h3>
                            <a href="{{ route('organizations.create') }}" class="text-sm bg-primary text-white px-3 py-1.5 rounded-lg hover:bg-primary/90 transition">
                                <i class="fas fa-plus mr-1"></i> Создать
                            </a>
                        </div>

                        @if($organizations->count())
                            <div class="space-y-3">
                                @foreach($organizations as $org)
                                    <a href="{{ route('organizations.show', $org) }}" class="block p-3 rounded-lg border border-primary/10 hover:bg-cream/50 transition">
                                        <div class="flex items-center gap-3">
                                            <x-org-avatar :organization="$org" size="sm" />
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-dark truncate">{{ $org->name }}</div>
                                                <div class="text-sm text-warm-gray">ИНН: {{ $org->inn }}</div>
                                            </div>
                                            @if($org->status === \App\Enums\OrganizationStatus::Verified)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 shrink-0">
                                                    <i class="fas fa-check-circle mr-1"></i> Верифицирована
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 shrink-0">
                                                    <i class="fas fa-clock mr-1"></i> На проверке
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-warm-gray text-sm">
                                У вас пока нет организаций. Создайте организацию, чтобы проводить конкурсы.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Мои участники (дети) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-dark">
                                <i class="fas fa-users text-gold mr-2"></i> Мои участники
                            </h3>
                            <a href="{{ route('participants.index') }}" class="text-sm text-primary hover:text-primary/80">
                                Управление <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>

                        @if($children->count())
                            <div class="space-y-3">
                                @foreach($children as $child)
                                    <div class="p-3 rounded-lg border border-primary/10">
                                        <div class="flex items-center gap-3">
                                            <x-user-avatar :user="$child" size="sm" />
                                            <div>
                                                <div class="font-medium text-dark">{{ $child->last_name }} {{ $child->first_name }}</div>
                                                <div class="text-sm text-warm-gray">{{ $child->email }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-warm-gray text-sm">
                                У вас пока нет добавленных участников.
                                <a href="{{ route('participants.index') }}" class="text-primary hover:underline">Добавить участника</a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Мои заявки + Мои дипломы -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Мои заявки -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-dark">
                                <i class="fas fa-file-alt text-gold mr-2"></i> Мои заявки
                            </h3>
                            <a href="{{ route('dashboard.applications') }}" class="text-sm text-primary hover:text-primary/80">
                                Все заявки <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>

                        @if($recentApplications->count())
                            <div class="space-y-2">
                                @foreach($recentApplications as $app)
                                    <div class="p-3 rounded-lg border border-primary/10">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <a href="{{ route('contests.show', $app->contest) }}"
                                                    class="font-medium text-dark hover:text-primary text-sm truncate block transition-colors">
                                                    {{ $app->contest->title }}
                                                </a>
                                                <p class="text-xs text-warm-gray mt-0.5">
                                                    {{ $app->created_at->format('d.m.Y') }}
                                                    @if($app->user_id !== auth()->id())
                                                        · {{ $app->user->first_name }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $app->status->color() }} shrink-0">
                                                {{ $app->status->label() }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-warm-gray text-sm">
                                Вы ещё не подавали заявок.
                                <a href="{{ route('contests.index') }}" class="text-primary hover:underline">Найти конкурсы</a>
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Мои дипломы -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-dark">
                                <i class="fas fa-award text-gold mr-2"></i> Мои награды
                            </h3>
                            <a href="{{ route('dashboard.diplomas') }}" class="text-sm text-primary hover:text-primary/80">
                                Все награды <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>

                        @if($recentDiplomas->count())
                            <div class="space-y-2">
                                @foreach($recentDiplomas as $diploma)
                                    <div class="p-3 rounded-lg border border-primary/10 flex items-center justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-dark text-sm truncate">{{ $diploma->contest->title }}</p>
                                            <p class="text-xs text-warm-gray mt-0.5">{{ $diploma->application->status->label() }}</p>
                                        </div>
                                        @if($diploma->file_path)
                                            <a href="{{ route('diplomas.download', $diploma) }}"
                                                class="shrink-0 text-xs text-primary hover:underline">
                                                <i class="fas fa-download mr-1"></i>Скачать
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-warm-gray text-sm">
                                Дипломы появятся здесь после завершения конкурсов.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
