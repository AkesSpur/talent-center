<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Мои организации</h2>
                <p class="text-warm-gray mt-1">Управление организациями и конкурсами</p>
            </div>
            <a href="{{ route('organizations.create') }}" class="self-start sm:self-auto px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i>Создать организацию
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('status') === 'organization-created')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 mb-6">
                    <i class="fas fa-check-circle mr-2"></i>Организация успешно создана
                </div>
            @endif

            @if($organizations->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($organizations as $organization)
                        <a href="{{ route('organizations.show', $organization) }}"
                            class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow border border-gold/10 block">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <x-org-avatar :organization="$organization" size="sm" />
                                    <div class="min-w-0">
                                        <h3 class="font-serif text-lg font-semibold text-dark truncate">{{ $organization->name }}</h3>
                                        <p class="text-warm-gray text-sm">ИНН: {{ $organization->inn }}</p>
                                    </div>
                                </div>
                                @if($organization->isVerified())
                                    <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-medium">
                                        <i class="fas fa-check-circle mr-1"></i>Верифицирована
                                    </span>
                                @else
                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-medium">
                                        <i class="fas fa-clock mr-1"></i>На проверке
                                    </span>
                                @endif
                            </div>
                            @if($organization->description)
                                <p class="text-warm-gray text-sm line-clamp-2">{{ $organization->description }}</p>
                            @endif
                            <div class="mt-4 flex items-center text-sm text-warm-gray">
                                <i class="fas fa-users mr-2"></i>{{ $organization->representatives->count() }} представител{{ $organization->representatives->count() === 1 ? 'ь' : 'ей' }}
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-building text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">У вас пока нет организаций</h3>
                    <p class="text-warm-gray mb-6">Создайте организацию для проведения конкурсов</p>
                    <a href="{{ route('organizations.create') }}" class="inline-block px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-plus mr-2"></i>Создать организацию
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
