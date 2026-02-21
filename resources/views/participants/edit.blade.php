<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <x-user-avatar :user="$participant" size="sm" />
                <div class="min-w-0">
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Редактировать участника</h2>
                    <p class="text-warm-gray mt-0.5 text-sm truncate">{{ $participant->last_name }} {{ $participant->first_name }}</p>
                </div>
            </div>
            <a href="{{ route('participants.index') }}" class="self-start sm:self-auto shrink-0 px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                <form method="POST" action="{{ route('participants.update', $participant) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-dark mb-2">Фамилия <span class="text-red-500">*</span></label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $participant->last_name) }}" required
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                            <x-input-error class="mt-1" :messages="$errors->get('last_name')" />
                        </div>
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-dark mb-2">Имя <span class="text-red-500">*</span></label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $participant->first_name) }}" required
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                            <x-input-error class="mt-1" :messages="$errors->get('first_name')" />
                        </div>
                        <div>
                            <label for="patronymic" class="block text-sm font-medium text-dark mb-2">Отчество</label>
                            <input id="patronymic" name="patronymic" type="text" value="{{ old('patronymic', $participant->patronymic) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-dark mb-2">Дата рождения</label>
                            <input id="birth_date" name="birth_date" type="date"
                                value="{{ old('birth_date', $participant->birth_date?->format('Y-m-d')) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                            <x-input-error class="mt-1" :messages="$errors->get('birth_date')" />
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-dark mb-2">Город</label>
                            <input id="city" name="city" type="text" value="{{ old('city', $participant->city) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="Москва" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="organization" class="block text-sm font-medium text-dark mb-2">Учреждение</label>
                            <input id="organization" name="organization" type="text" value="{{ old('organization', $participant->organization) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="ДШИ №1" />
                        </div>
                        <div>
                            <label for="group" class="block text-sm font-medium text-dark mb-2">Класс / группа</label>
                            <input id="group" name="group" type="text" value="{{ old('group', $participant->group) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="3А" />
                        </div>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="submit"
                            class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                            <i class="fas fa-save mr-2"></i>Сохранить изменения
                        </button>
                        <a href="{{ route('participants.index') }}"
                            class="px-6 py-3 border border-primary/20 text-warm-gray rounded-lg hover:bg-cream/50 transition-colors">
                            Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
