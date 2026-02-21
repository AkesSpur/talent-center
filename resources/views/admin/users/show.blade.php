<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <x-user-avatar :user="$user" size="lg" class="shrink-0" />
                <div class="min-w-0">
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark break-words">{{ $user->full_name }}</h2>
                    <p class="text-warm-gray text-sm mt-0.5 truncate">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">
                    <i class="fas fa-pen mr-2"></i>Редактировать
                </a>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>К списку
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Confirm Block/Unblock Modal --}}
    <x-confirm-modal
        name="toggle-block-user"
        title="{{ $user->is_blocked ? 'Разблокировать пользователя' : 'Заблокировать пользователя' }}"
        message="{{ $user->is_blocked
            ? 'Вы уверены, что хотите разблокировать пользователя «' . $user->full_name . '»? Пользователь снова сможет входить в систему.'
            : 'Вы уверены, что хотите заблокировать пользователя «' . $user->full_name . '»? Пользователь не сможет войти в систему.' }}"
        icon="{{ $user->is_blocked ? 'fa-unlock' : 'fa-ban' }}"
        iconColor="{{ $user->is_blocked ? 'text-green-600' : 'text-red-600' }}"
        iconBg="{{ $user->is_blocked ? 'bg-green-100' : 'bg-red-100' }}"
        confirmText="{{ $user->is_blocked ? 'Разблокировать' : 'Заблокировать' }}"
        confirmClass="{{ $user->is_blocked ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}"
        method="PUT"
    >
        <input type="hidden" name="is_blocked" value="{{ $user->is_blocked ? '0' : '1' }}">
    </x-confirm-modal>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: User Info --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Personal Info --}}
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-serif text-xl font-semibold text-dark mb-4">Личная информация</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-warm-gray">Фамилия:</span>
                                <span class="text-dark ml-2">{{ $user->last_name }}</span>
                            </div>
                            <div>
                                <span class="text-warm-gray">Имя:</span>
                                <span class="text-dark ml-2">{{ $user->first_name }}</span>
                            </div>
                            @if($user->patronymic)
                                <div>
                                    <span class="text-warm-gray">Отчество:</span>
                                    <span class="text-dark ml-2">{{ $user->patronymic }}</span>
                                </div>
                            @endif
                            @if($user->phone)
                                <div>
                                    <span class="text-warm-gray">Телефон:</span>
                                    <span class="text-dark ml-2">{{ $user->phone }}</span>
                                </div>
                            @endif
                            @if($user->city)
                                <div>
                                    <span class="text-warm-gray">Город:</span>
                                    <span class="text-dark ml-2">{{ $user->city }}</span>
                                </div>
                            @endif
                            @if($user->country)
                                <div>
                                    <span class="text-warm-gray">Страна:</span>
                                    <span class="text-dark ml-2">{{ $user->country }}</span>
                                </div>
                            @endif
                            @if($user->education)
                                <div class="md:col-span-2">
                                    <span class="text-warm-gray">Образование:</span>
                                    <span class="text-dark ml-2">{{ $user->education }}</span>
                                </div>
                            @endif
                            @if($user->bio)
                                <div class="md:col-span-2">
                                    <span class="text-warm-gray">О себе:</span>
                                    <p class="text-dark mt-1">{{ $user->bio }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Organizations --}}
                    @if($user->organizations->count())
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="font-serif text-xl font-semibold text-dark mb-4">Организации</h3>
                            <div class="space-y-3">
                                @foreach($user->organizations as $org)
                                    <div class="flex items-center justify-between p-3 border border-primary/10 rounded-lg {{ $org->isBlocked() ? 'opacity-60' : '' }}">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.organizations.show', $org) }}" class="font-medium text-primary hover:underline">{{ $org->name }}</a>
                                                @if($org->isBlocked())
                                                    <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full">
                                                        <i class="fas fa-ban mr-0.5"></i>Заблокирована
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-warm-gray">ИНН: {{ $org->inn }}</p>
                                        </div>
                                        <div class="flex gap-1.5">
                                            @if($org->pivot->can_create)
                                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full" title="Создание конкурсов">
                                                    <i class="fas fa-plus-circle mr-0.5"></i> Создание
                                                </span>
                                            @endif
                                            @if($org->pivot->can_manage)
                                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full" title="Управление организацией">
                                                    <i class="fas fa-cog mr-0.5"></i> Управление
                                                </span>
                                            @endif
                                            @if($org->pivot->can_evaluate)
                                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full" title="Оценка заявок">
                                                    <i class="fas fa-star mr-0.5"></i> Оценка
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Children --}}
                    @if($user->children->count())
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="font-serif text-xl font-semibold text-dark mb-4">Участники (дети)</h3>
                            <div class="space-y-2">
                                @foreach($user->children as $child)
                                    <div class="flex items-center gap-3 p-3 border border-primary/10 rounded-lg">
                                        <x-user-avatar :user="$child" size="sm" />
                                        <div>
                                            <a href="{{ route('admin.users.show', $child) }}" class="font-medium text-primary hover:underline text-sm">{{ $child->full_name }}</a>
                                            <p class="text-xs text-warm-gray">
                                                @if($child->birth_date) {{ $child->birth_date->age }} лет @endif
                                                @if($child->city) &bull; {{ $child->city }} @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right: Actions --}}
                <div class="space-y-6">
                    {{-- Status Card --}}
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-semibold text-dark mb-4">Статус аккаунта</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Роль:</span>
                                <span class="font-medium text-dark">{{ $user->role->label() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Статус:</span>
                                @if($user->is_blocked)
                                    <span class="text-red-600 font-medium">Заблокирован</span>
                                @else
                                    <span class="text-green-600 font-medium">Активен</span>
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Email подтверждён:</span>
                                @if($user->email_verified_at)
                                    <span class="text-green-600">Да</span>
                                @else
                                    <span class="text-yellow-600">Нет</span>
                                @endif
                            </div>
                            <div class="flex justify-between">
                                <span class="text-warm-gray">Регистрация:</span>
                                <span class="text-dark">{{ $user->created_at->format('d.m.Y') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div x-data class="bg-white rounded-xl shadow-lg p-6 space-y-4">
                        <h3 class="font-semibold text-dark mb-2">Быстрые действия</h3>

                        {{-- Change Role --}}
                        <form method="POST" action="{{ route('admin.users.update', $user) }}">
                            @csrf
                            @method('PUT')
                            <label class="block text-sm font-medium text-dark mb-1.5">Изменить роль</label>
                            <div class="flex gap-2">
                                <select name="role" class="flex-1 px-3 py-2 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    @foreach(\App\Enums\UserRole::cases() as $role)
                                        <option value="{{ $role->value }}" {{ $user->role === $role ? 'selected' : '' }}>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90 transition-colors">
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                        </form>

                        {{-- Block/Unblock --}}
                        <button type="button"
                            @click="$dispatch('confirm-toggle-block-user', { action: '{{ route('admin.users.update', $user) }}' })"
                            class="w-full py-2.5 {{ $user->is_blocked ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} text-white font-medium rounded-lg transition-colors text-sm">
                            @if($user->is_blocked)
                                <i class="fas fa-unlock mr-2"></i>Разблокировать
                            @else
                                <i class="fas fa-ban mr-2"></i>Заблокировать
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
