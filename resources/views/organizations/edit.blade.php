<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Редактировать организацию</h2>
                <p class="text-warm-gray mt-1 truncate">{{ $organization->name }}</p>
            </div>
            <a href="{{ route('organizations.show', $organization) }}" class="self-start sm:self-auto px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm shrink-0">
                <i class="fas fa-arrow-left mr-2"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                <form method="POST" action="{{ route('organizations.update', $organization) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Avatar Upload --}}
                    <div x-data="{ preview: null, hasAvatar: {{ $organization->avatar_path ? 'true' : 'false' }} }">
                        <label class="block text-sm font-medium text-dark mb-3">Логотип организации</label>
                        <div class="flex items-center gap-5">
                            <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 relative">
                                <template x-if="preview">
                                    <img :src="preview" class="w-full h-full object-cover" />
                                </template>
                                <template x-if="!preview && hasAvatar">
                                    <img src="{{ $organization->avatar_url }}" class="w-full h-full object-cover" alt="{{ $organization->name }}" />
                                </template>
                                <template x-if="!preview && !hasAvatar">
                                    <div class="w-full h-full gradient-gold flex items-center justify-center text-white font-bold text-xl">
                                        {{ $organization->initials }}
                                    </div>
                                </template>
                            </div>
                            <div class="space-y-2">
                                <label for="org-avatar-input" class="inline-flex items-center gap-2 px-4 py-2 border border-primary/30 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 cursor-pointer transition-colors">
                                    <i class="fas fa-camera"></i> Изменить логотип
                                </label>
                                <input id="org-avatar-input" name="avatar" type="file" accept="image/*" class="hidden"
                                    @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null; if(preview) hasAvatar = false;">
                                <p class="text-xs text-warm-gray">JPG, PNG, WebP — до 4 МБ</p>
                                @if($organization->avatar_path)
                                    <label class="flex items-center gap-1.5 cursor-pointer text-xs text-red-500 hover:text-red-700">
                                        <input type="checkbox" name="delete_avatar" value="1" class="rounded border-red-300 text-red-500 focus:ring-red-400 w-3.5 h-3.5"
                                            @change="if($event.target.checked){ hasAvatar = false; preview = null; } else { hasAvatar = true; }">
                                        Удалить логотип
                                    </label>
                                @endif
                            </div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-dark mb-2">Название организации <span class="text-red-500">*</span></label>
                        <input id="name" name="name" type="text" value="{{ old('name', $organization->name) }}" required
                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            placeholder="Детская школа искусств №1" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-dark mb-2">Описание</label>
                        <textarea id="description" name="description" rows="3"
                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-none"
                            placeholder="Краткое описание деятельности организации...">{{ old('description', $organization->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="inn" class="block text-sm font-medium text-dark mb-2">ИНН <span class="text-red-500">*</span></label>
                            <input id="inn" name="inn" type="text" value="{{ old('inn', $organization->inn) }}" required
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="1234567890" />
                            <x-input-error class="mt-2" :messages="$errors->get('inn')" />
                        </div>
                        <div>
                            <label for="ogrn" class="block text-sm font-medium text-dark mb-2">ОГРН</label>
                            <input id="ogrn" name="ogrn" type="text" value="{{ old('ogrn', $organization->ogrn) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="1234567890123" />
                            <x-input-error class="mt-2" :messages="$errors->get('ogrn')" />
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-dark mb-2">Контактный телефон</label>
                            <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $organization->contact_phone) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="+7 (999) 123-45-67" />
                            <x-input-error class="mt-2" :messages="$errors->get('contact_phone')" />
                        </div>
                        <div>
                            <label for="website" class="block text-sm font-medium text-dark mb-2">Веб-сайт</label>
                            <input id="website" name="website" type="url" value="{{ old('website', $organization->website) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="https://example.ru" />
                            <x-input-error class="mt-2" :messages="$errors->get('website')" />
                        </div>
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-dark mb-2">Контактный email <span class="text-red-500">*</span></label>
                        <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $organization->contact_email) }}" required
                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            placeholder="info@organization.ru" />
                        <x-input-error class="mt-2" :messages="$errors->get('contact_email')" />
                    </div>

                    <div>
                        <label for="legal_address" class="block text-sm font-medium text-dark mb-2">Юридический адрес</label>
                        <input id="legal_address" name="legal_address" type="text" value="{{ old('legal_address', $organization->legal_address) }}"
                            class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            placeholder="г. Москва, ул. Примерная, д. 1" />
                        <x-input-error class="mt-2" :messages="$errors->get('legal_address')" />
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                            Сохранить изменения
                        </button>
                        <a href="{{ route('organizations.show', $organization) }}" class="px-6 py-3 border border-primary/30 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors">
                            Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
