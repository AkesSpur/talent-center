<x-app-layout>
    <x-slot name="header">
        <div x-data class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Организации</h2>
                <p class="text-warm-gray text-sm mt-0.5">Всего в системе: <span class="font-semibold text-dark">{{ $totalCount }}</span></p>
            </div>
            <button type="button"
                @click="$dispatch('open-create-org-modal')"
                class="self-start sm:self-auto px-4 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm whitespace-nowrap">
                <i class="fas fa-building mr-2"></i>Добавить организацию
            </button>
        </div>
    </x-slot>

    {{-- Create Organization Modal --}}
    <div x-data="{ open: false }"
         @open-create-org-modal.window="open = true"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
         style="display: none;">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-dark/40 backdrop-blur-sm" @click="open = false"></div>

        {{-- Modal --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto my-8"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-serif text-xl font-bold text-dark">Добавить организацию</h3>
                    <button type="button" @click="open = false"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-warm-gray hover:text-dark hover:bg-cream transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4 text-red-700 text-sm">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.organizations.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Название организации <span class="text-red-500">*</span></label>
                        <input name="name" type="text" value="{{ old('name') }}" required
                            placeholder="ООО Талант-Центр"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Описание</label>
                        <textarea name="description" rows="2"
                            placeholder="Краткое описание организации..."
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-none">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">ИНН <span class="text-red-500">*</span></label>
                            <input name="inn" type="text" value="{{ old('inn') }}" required
                                placeholder="1234567890"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">ОГРН</label>
                            <input name="ogrn" type="text" value="{{ old('ogrn') }}"
                                placeholder="1234567890123"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Юридический адрес</label>
                        <input name="legal_address" type="text" value="{{ old('legal_address') }}"
                            placeholder="г. Москва, ул. Примерная, д. 1"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input name="contact_email" type="email" value="{{ old('contact_email') }}" required
                                placeholder="info@example.com"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Телефон</label>
                            <input name="contact_phone" type="tel" value="{{ old('contact_phone') }}"
                                placeholder="+7 (999) 123-45-67"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Сайт</label>
                        <input name="website" type="url" value="{{ old('website') }}"
                            placeholder="https://example.com"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Статус</label>
                        <select name="status"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>На проверке</option>
                            <option value="verified" {{ old('status') === 'verified' ? 'selected' : '' }}>Верифицирована</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="open = false"
                            class="px-4 py-2.5 text-warm-gray hover:text-dark border border-primary/20 rounded-lg text-sm transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                            <i class="fas fa-plus mr-1.5"></i>Создать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

            @if(session('status') === 'organization-created')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 mb-6">
                    <i class="fas fa-check-circle mr-2"></i>Организация успешно создана
                </div>
            @endif

            @if(session('status') === 'organization-verified')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 mb-6">
                    <i class="fas fa-check-circle mr-2"></i>Организация верифицирована
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-lg p-6">

                {{-- Search & Filter --}}
                <form method="GET" action="{{ route('admin.organizations.index') }}" class="flex flex-wrap gap-4 mb-6">
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
                        <a href="{{ route('admin.organizations.index') }}" class="px-5 py-2.5 border border-primary/30 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                            Сбросить
                        </a>
                    @endif
                </form>

                {{-- Table --}}
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
                                                    @click="$dispatch('confirm-quick-verify', { action: '{{ route('admin.organizations.verify', $org) }}' })"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg border border-green-300 text-green-600 hover:bg-green-50 transition-colors">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('admin.organizations.edit', $org) }}" title="Редактировать"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-primary/20 text-primary hover:bg-primary/5 transition-colors">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <a href="{{ route('admin.organizations.show', $org) }}" title="Просмотр"
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
