<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Пользователи</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg p-6">

                <form method="GET" action="{{ route('support.users.index') }}" class="flex flex-wrap gap-4 mb-6">
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
                        <a href="{{ route('support.users.index') }}" class="px-5 py-2.5 border border-primary/30 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                            Сбросить
                        </a>
                    @endif
                </form>

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
                                    <td class="py-3 px-4 text-warm-gray">{{ $u->email }}</td>
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
                                            <a href="{{ route('support.users.show', $u) }}" title="Просмотр"
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

                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
