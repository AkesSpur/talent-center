<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <x-user-avatar :user="$user" size="sm" />
                <div class="min-w-0">
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Редактирование пользователя</h2>
                    <p class="text-warm-gray mt-0.5 text-sm truncate">{{ $user->full_name }}</p>
                </div>
            </div>
            <a href="{{ route('admin.users.show', $user) }}" class="self-start sm:self-auto shrink-0 px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Avatar --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-serif text-xl font-semibold text-dark mb-4">Фото профиля</h3>
                    <div class="flex items-center gap-6">
                        <x-user-avatar :user="$user" size="xl" />
                        <div>
                            <input type="file" name="avatar" accept="image/*"
                                class="text-sm text-warm-gray file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
                            <p class="text-xs text-warm-gray mt-1">JPEG, PNG, GIF, WebP. Макс. 4 МБ. Конвертируется в WebP.</p>
                            @error('avatar')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Personal Info --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-serif text-xl font-semibold text-dark mb-4">Личная информация</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-dark mb-2">Фамилия <span class="text-red-500">*</span></label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" required
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                            @error('last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-dark mb-2">Имя <span class="text-red-500">*</span></label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" required
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                            @error('first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="patronymic" class="block text-sm font-medium text-dark mb-2">Отчество</label>
                            <input id="patronymic" name="patronymic" type="text" value="{{ old('patronymic', $user->patronymic) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-dark mb-2">Email <span class="text-red-500">*</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                            @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-dark mb-2">Телефон</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-dark mb-2">Город</label>
                            <input id="city" name="city" type="text" value="{{ old('city', $user->city) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label for="country" class="block text-sm font-medium text-dark mb-2">Страна</label>
                            <input id="country" name="country" type="text" value="{{ old('country', $user->country) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label for="education" class="block text-sm font-medium text-dark mb-2">Образование</label>
                            <input id="education" name="education" type="text" value="{{ old('education', $user->education) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                    </div>
                    <div class="mt-6">
                        <label for="bio" class="block text-sm font-medium text-dark mb-2">Биография</label>
                        <textarea id="bio" name="bio" rows="3"
                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-none">{{ old('bio', $user->bio) }}</textarea>
                    </div>
                </div>

                {{-- Role & Status --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-serif text-xl font-semibold text-dark mb-4">Роль и статус</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-dark mb-2">Роль</label>
                            <select id="role" name="role"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                @foreach(\App\Enums\UserRole::cases() as $role)
                                    <option value="{{ $role->value }}" {{ $user->role === $role ? 'selected' : '' }}>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center pt-8">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="is_blocked" value="0">
                                <input type="checkbox" name="is_blocked" value="1" {{ $user->is_blocked ? 'checked' : '' }}
                                    class="rounded border-red-300 text-red-600 focus:ring-red-500 w-5 h-5" />
                                <span class="text-sm font-medium text-dark">Заблокировать пользователя</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit"
                        class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-save mr-2"></i>Сохранить изменения
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}"
                        class="px-6 py-3 border border-primary/20 text-warm-gray rounded-lg hover:bg-cream/50 transition-colors">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
