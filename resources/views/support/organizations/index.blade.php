<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Организации</h2>
    </x-slot>

    {{-- Quick Verify Confirmation Modal --}}
    <x-confirm-modal
        name="quick-verify"
        title="Верифицировать организацию"
        message="Вы уверены, что хотите верифицировать эту организацию? После верификации она сможет создавать конкурсы."
        icon="fa-check-circle"
        iconColor="text-green-600"
        iconBg="bg-green-100"
        confirmText="Верифицировать"
    />

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('status') === 'organization-verified')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 mb-6">
                    <i class="fas fa-check-circle mr-2"></i>Организация верифицирована
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-lg p-6">

                <form method="GET" action="{{ route('support.organizations.index') }}" class="flex flex-wrap gap-4 mb-6">
                    <div class="flex-1 min-w-[200px]">
                        <input name="search" type="text" value="{{ request('search') }}"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            placeholder="Поиск по названию или ИНН..." />
                    </div>
                    <select name="status" class="px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        <option value="">Все статусы</option>
                        @foreach(\App\Enums\OrganizationStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Заблокированные</option>
                    </select>
                    <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-search mr-2"></i>Найти
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('support.organizations.index') }}" class="px-5 py-2.5 border border-primary/30 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                            Сбросить
                        </a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-primary/10 bg-cream/40">
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Организация</th>
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">ИНН</th>
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Статус</th>
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Создатель</th>
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Создана</th>
                                <th class="py-2.5 px-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/10">
                            @forelse($organizations as $org)
                                <tr class="hover:bg-cream/50 transition-colors {{ $org->isBlocked() ? 'opacity-60' : '' }}">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <x-org-avatar :organization="$org" size="sm" />
                                            <span class="font-medium text-dark">{{ $org->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-warm-gray">{{ $org->inn }}</td>
                                    <td class="py-3 px-4">
                                        @if($org->isBlocked())
                                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                                                <i class="fas fa-ban mr-1"></i>Заблокирована
                                            </span>
                                        @elseif($org->isVerified())
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                                <i class="fas fa-check-circle mr-1"></i>Верифицирована
                                            </span>
                                        @else
                                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">
                                                <i class="fas fa-clock mr-1"></i>На проверке
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-warm-gray">{{ $org->createdBy?->full_name ?? '—' }}</td>
                                    <td class="py-3 px-4 text-warm-gray">{{ $org->created_at->format('d.m.Y') }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div x-data class="flex items-center justify-end gap-2">
                                            @if(!$org->isVerified() && !$org->isBlocked())
                                                <button type="button" title="Верифицировать"
                                                    @click="$dispatch('confirm-quick-verify', { action: '{{ route('support.organizations.verify', $org) }}' })"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-green-300 text-green-600 hover:bg-green-50 transition-colors">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('support.organizations.show', $org) }}" title="Просмотр"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-warm-gray">Организации не найдены</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $organizations->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
