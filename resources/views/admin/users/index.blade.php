<x-app-layout>
    <x-slot name="header">
        <div x-data class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Пользователи</h2>
                <p class="text-warm-gray text-sm mt-0.5">Всего в системе: <span class="font-semibold text-dark">{{ $totalCount }}</span></p>
            </div>
            <button type="button"
                @click="$dispatch('open-create-user-modal')"
                class="self-start sm:self-auto px-4 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm whitespace-nowrap">
                <i class="fas fa-user-plus mr-2"></i>Создать пользователя
            </button>
        </div>
    </x-slot>

    {{-- Create User Modal --}}
    <div x-data="{ open: false }"
         @open-create-user-modal.window="open = true"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-dark/40 backdrop-blur-sm" @click="open = false"></div>

        {{-- Modal --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-serif text-xl font-bold text-dark">Создать пользователя</h3>
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

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input name="email" type="email" value="{{ old('email') }}" required
                            placeholder="user@example.com"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Телефон</label>
                        <input name="phone" type="tel" value="{{ old('phone') }}"
                            placeholder="+7 (999) 123-45-67"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Имя <span class="text-red-500">*</span></label>
                            <input name="first_name" type="text" value="{{ old('first_name') }}" required
                                placeholder="Иван"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Фамилия <span class="text-red-500">*</span></label>
                            <input name="last_name" type="text" value="{{ old('last_name') }}" required
                                placeholder="Иванов"
                                class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Роль</label>
                        <select name="role"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                            @foreach(\App\Enums\UserRole::cases() as $role)
                                <option value="{{ $role->value }}" {{ old('role', 'participant') === $role->value ? 'selected' : '' }}>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Пароль <span class="text-red-500">*</span></label>
                        <input name="password" type="password" required
                            placeholder="Минимум 6 символов"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
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

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-xl shadow-lg p-6">

                {{-- Search & Filter --}}
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4 mb-6">
                    <div class="flex-1 min-w-[200px]">
                        <input name="search" type="text" value="{{ request('search') }}"
                            class="w-full px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            placeholder="Поиск по имени или email..." />
                    </div>
                    <select name="role" class="px-4 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        <option value="">Все роли</option>
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-search mr-2"></i>Найти
                    </button>
                    @if(request('search') || request('role'))
                        <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 border border-primary/30 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                            Сбросить
                        </a>
                    @endif
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-primary/10 bg-cream/40">
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Пользователь</th>
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Email</th>
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Роль</th>
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Статус</th>
                                <th class="text-left py-2.5 px-4 text-xs font-semibold text-warm-gray uppercase tracking-wide">Регистрация</th>
                                <th class="py-2.5 px-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/10">
                            @forelse($users as $u)
                                <tr class="hover:bg-cream/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <x-user-avatar :user="$u" size="sm" />
                                            <span class="font-medium text-dark">{{ $u->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-warm-gray">
                                        <div class="flex items-center gap-1.5" x-data="{ copied: false }">
                                            <span>{{ $u->email }}</span>
                                            <button type="button" title="Скопировать email"
                                                @click="navigator.clipboard.writeText('{{ $u->email }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                class="text-warm-gray/50 hover:text-primary transition-colors shrink-0">
                                                <i class="fas text-xs" :class="copied ? 'fa-check text-green-500' : 'fa-copy'"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $roleColors = [
                                                'admin' => 'bg-red-100 text-red-700',
                                                'support' => 'bg-blue-100 text-blue-700',
                                                'participant' => 'bg-green-100 text-green-700',
                                            ];
                                        @endphp
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $roleColors[$u->role->value] ?? '' }}">
                                            {{ $u->role->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($u->is_blocked)
                                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Заблокирован</span>
                                        @else
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Активен</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-warm-gray">{{ $u->created_at->format('d.m.Y') }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.users.edit', $u) }}" title="Редактировать"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-primary/20 text-primary hover:bg-primary/5 transition-colors">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <a href="{{ route('admin.users.show', $u) }}" title="Просмотр"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-warm-gray">Пользователи не найдены</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
