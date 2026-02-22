<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Профили организаторов конкурсов (ЮЛ)</h2>
                <p class="text-warm-gray mt-1">Список организаций, представителем которых являетесь вы</p>
            </div>
            <a href="{{ route('organizations.create') }}" class="self-start sm:self-auto px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i>Создать организацию
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($organizations->count() > 0)
                <div class="bg-white rounded-xl shadow-lg border border-gold/10 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                <th class="text-left px-6 py-3 font-semibold">Организация</th>
                                <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Контакты</th>
                                <th class="text-left px-6 py-3 font-semibold">Статус</th>
                                <th class="text-right px-6 py-3 font-semibold">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gold/10">
                            @foreach($organizations as $organization)
                                <tr class="hover:bg-cream/50 transition-colors {{ $organization->isBlocked() ? 'opacity-60' : '' }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('organizations.show', $organization) }}" class="text-warm-gray hover:text-primary transition-colors shrink-0">
                                                <i class="fas fa-chevron-right text-xs"></i>
                                            </a>
                                            <x-org-avatar :organization="$organization" size="sm" />
                                            <div class="min-w-0">
                                                <a href="{{ route('organizations.show', $organization) }}" class="font-semibold text-dark hover:text-primary transition-colors block truncate">
                                                    {{ $organization->name }}
                                                </a>
                                                <p class="text-warm-gray text-xs">ИНН: {{ $organization->inn }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 hidden md:table-cell text-warm-gray">
                                        @if($organization->email || $organization->phone)
                                            @if($organization->phone)
                                                <div class="flex items-center gap-1.5">
                                                    <i class="fas fa-phone text-xs"></i>
                                                    <span>{{ $organization->phone }}</span>
                                                </div>
                                            @endif
                                            @if($organization->email)
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <i class="fas fa-envelope text-xs"></i>
                                                    <span>{{ $organization->email }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="italic text-warm-gray/60">Не указаны</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($organization->isBlocked())
                                            <span class="inline-flex items-center gap-1 text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-medium whitespace-nowrap">
                                                <i class="fas fa-ban"></i>Заблокирована
                                            </span>
                                        @elseif($organization->isVerified())
                                            <span class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-medium whitespace-nowrap">
                                                <i class="fas fa-check-circle"></i>Верифицирована
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-medium whitespace-nowrap">
                                                <i class="fas fa-clock"></i>На проверке
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('organizations.show', $organization) }}"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gold/30 text-warm-gray hover:text-primary hover:border-primary/40 transition-colors"
                                               title="Просмотр">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <a href="{{ route('organizations.edit', $organization) }}"
                                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gold/30 text-warm-gray hover:text-primary hover:border-primary/40 transition-colors"
                                               title="Редактировать">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
