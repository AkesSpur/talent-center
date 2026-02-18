<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-serif text-3xl font-bold text-dark">Мой профиль</h2>
            <p class="text-warm-gray mt-1">Управление личными данными и участниками</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Left Column: Profile Card + Stats --}}
                <div class="lg:col-span-1">
                    {{-- Profile Card --}}
                    <div class="bg-white rounded-xl shadow-lg p-6 text-center mb-6">
                        <div class="relative inline-block mb-4">
                            <div class="w-32 h-32 gradient-gold rounded-full flex items-center justify-center mx-auto border-4 border-white shadow-lg">
                                <i class="fas fa-user text-4xl text-white"></i>
                            </div>
                        </div>
                        <h2 class="font-serif text-xl font-semibold text-dark mb-1">{{ $user->full_name }}</h2>
                        <p class="text-warm-gray text-sm mb-4">
                            @if($user->isAdmin()) Администратор
                            @elseif($user->isSupport()) Поддержка
                            @else Конкурсант
                            @endif
                        </p>
                        <div class="flex justify-center space-x-2">
                            @if($user->hasVerifiedEmail())
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                                    <i class="fas fa-check mr-1"></i> Email подтверждён
                                </span>
                            @else
                                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Email не подтверждён
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-semibold text-dark mb-4">Статистика</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-warm-gray text-sm">Участников</span>
                                <span class="font-semibold text-dark">{{ $participants->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-warm-gray text-sm">Заявок</span>
                                <span class="font-semibold text-dark">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Tabs + Content --}}
                <div class="lg:col-span-2 space-y-8" x-data="{ tab: '{{ $errors->updatePassword->any() ? 'password' : 'profile' }}' }">

                    {{-- Tabs --}}
                    <div class="border-b border-primary/20">
                        <nav class="flex space-x-6 overflow-x-auto">
                            <button @click="tab = 'profile'"
                                :class="tab === 'profile' ? 'border-primary text-primary' : 'border-transparent text-warm-gray hover:text-primary'"
                                class="py-4 px-1 border-b-2 font-medium whitespace-nowrap transition-colors">
                                <i class="fas fa-user mr-2"></i>Личные данные
                            </button>
                            <button @click="tab = 'participants'"
                                :class="tab === 'participants' ? 'border-primary text-primary' : 'border-transparent text-warm-gray hover:text-primary'"
                                class="py-4 px-1 border-b-2 font-medium whitespace-nowrap transition-colors">
                                <i class="fas fa-users mr-2"></i>Мои участники
                            </button>
                            <button @click="tab = 'password'"
                                :class="tab === 'password' ? 'border-primary text-primary' : 'border-transparent text-warm-gray hover:text-primary'"
                                class="py-4 px-1 border-b-2 font-medium whitespace-nowrap transition-colors">
                                <i class="fas fa-lock mr-2"></i>Безопасность
                            </button>
                        </nav>
                    </div>

                    {{-- Tab: Personal Info --}}
                    <div x-show="tab === 'profile'" x-cloak>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="font-serif text-xl font-semibold text-dark mb-6">Личные данные</h3>

                            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                                @csrf
                            </form>

                            <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                                @csrf
                                @method('patch')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-dark mb-2">Фамилия</label>
                                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" required
                                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                        <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                                    </div>
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-dark mb-2">Имя</label>
                                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" required
                                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                        <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="patronymic" class="block text-sm font-medium text-dark mb-2">Отчество</label>
                                        <input id="patronymic" name="patronymic" type="text" value="{{ old('patronymic', $user->patronymic) }}"
                                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                        <x-input-error class="mt-2" :messages="$errors->get('patronymic')" />
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-dark mb-2">Телефон</label>
                                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-dark mb-2">Email</label>
                                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                        <div class="mt-2">
                                            <p class="text-sm text-dark">
                                                Ваш email не подтверждён.
                                                <button form="send-verification" class="underline text-sm text-warm-gray hover:text-primary">
                                                    Отправить письмо повторно.
                                                </button>
                                            </p>
                                            @if (session('status') === 'verification-link-sent')
                                                <p class="mt-2 font-medium text-sm text-green-600">
                                                    Новая ссылка подтверждения отправлена на ваш email.
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Success/Error Messages --}}
                                @if (session('status') === 'profile-updated')
                                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                                        class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700">
                                        <i class="fas fa-check-circle mr-2"></i>Профиль успешно обновлён
                                    </div>
                                @endif

                                <div class="flex gap-4">
                                    <button type="submit"
                                        class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                                        Сохранить изменения
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Tab: Participants --}}
                    <div x-show="tab === 'participants'" x-cloak>
                        @include('profile.partials.participants-section')
                    </div>

                    {{-- Tab: Security (Password + Delete) --}}
                    <div x-show="tab === 'password'" x-cloak>
                        {{-- Change Password --}}
                        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                            <h3 class="font-serif text-xl font-semibold text-dark mb-6">Изменить пароль</h3>
                            <p class="text-warm-gray text-sm mb-6">Используйте длинный случайный пароль для надёжной защиты аккаунта.</p>

                            <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                                @csrf
                                @method('put')

                                <div>
                                    <label for="update_password_current_password" class="block text-sm font-medium text-dark mb-2">Текущий пароль</label>
                                    <input id="update_password_current_password" name="current_password" type="password"
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="update_password_password" class="block text-sm font-medium text-dark mb-2">Новый пароль</label>
                                        <input id="update_password_password" name="password" type="password"
                                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                                    </div>
                                    <div>
                                        <label for="update_password_password_confirmation" class="block text-sm font-medium text-dark mb-2">Подтвердите пароль</label>
                                        <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>

                                @if (session('status') === 'password-updated')
                                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                                        class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700">
                                        <i class="fas fa-check-circle mr-2"></i>Пароль успешно изменён
                                    </div>
                                @endif

                                <div>
                                    <button type="submit"
                                        class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                                        Сохранить пароль
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Delete Account --}}
                        <div class="bg-white rounded-xl shadow-lg p-6 border border-red-100">
                            <h3 class="font-serif text-xl font-semibold text-dark mb-2">Удаление аккаунта</h3>
                            <p class="text-warm-gray text-sm mb-6">После удаления аккаунта все данные будут безвозвратно удалены. Перед удалением сохраните все важные данные.</p>

                            <x-danger-button
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                            >Удалить аккаунт</x-danger-button>

                            <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                                <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                                    @csrf
                                    @method('delete')

                                    <h2 class="text-lg font-medium text-dark">
                                        Вы уверены, что хотите удалить аккаунт?
                                    </h2>
                                    <p class="mt-1 text-sm text-warm-gray">
                                        После удаления все данные будут безвозвратно утеряны. Введите ваш пароль для подтверждения.
                                    </p>

                                    <div class="mt-6">
                                        <x-input-label for="password" value="Пароль" class="sr-only" />
                                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-3/4" placeholder="Пароль" />
                                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <x-secondary-button x-on:click="$dispatch('close')">
                                            Отмена
                                        </x-secondary-button>
                                        <x-danger-button class="ms-3">
                                            Удалить аккаунт
                                        </x-danger-button>
                                    </div>
                                </form>
                            </x-modal>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
