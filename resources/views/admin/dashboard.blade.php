<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif font-semibold text-xl text-dark leading-tight">
            Панель администратора
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-users mr-1"></i> Пользователи</div>
                    <div class="mt-1 text-3xl font-semibold text-dark">{{ $usersCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b-4 border-gold">
                    <div class="text-sm font-medium text-warm-gray"><i class="fas fa-building mr-1"></i> Организации</div>
                    <div class="mt-1 text-3xl font-semibold text-dark">{{ $organizationsCount }}</div>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                <div class="p-6 text-dark">
                    <h3 class="text-lg font-medium mb-2">Системное администрирование</h3>
                    <p class="text-warm-gray">
                        Управление пользователями, верификация организаций и контроль конкурсов появятся на следующих этапах разработки.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
