<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif font-semibold text-xl text-dark leading-tight">
            Личный кабинет
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gold/10">
                <div class="p-6 text-dark">
                    <h3 class="text-lg font-medium mb-2">
                        <i class="fas fa-hand-wave text-gold mr-2"></i>
                        Добро пожаловать, {{ auth()->user()->first_name }}!
                    </h3>
                    <p class="text-warm-gray">
                        Это ваш личный кабинет участника. Просмотр конкурсов и подача заявок появятся на следующих этапах разработки.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
