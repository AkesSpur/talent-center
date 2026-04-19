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
                @php
                    $initMinor = $participant->birth_date
                        ? now() < $participant->birth_date->copy()->addYears(18)->addDay()
                        : false;
                    $hasConsent = (bool) $participant->parental_consent_path;
                    $consentTemplateExists = \App\Models\SiteSettings::get(\App\Models\SiteSettings::PARENTAL_CONSENT_DOCUMENT);
                @endphp
                <form method="POST" action="{{ route('participants.update', $participant) }}"
                      enctype="multipart/form-data"
                      class="space-y-5"
                      x-data="{
                          minor: {{ $initMinor ? 'true' : 'false' }},
                          hasConsent: {{ $hasConsent ? 'true' : 'false' }},
                          consentFile: '',
                          isMinorCheck(v) {
                              if (!v) return false;
                              const d = new Date(v), t = new Date(d);
                              t.setFullYear(t.getFullYear() + 18); t.setDate(t.getDate() + 1);
                              return new Date() < t;
                          }
                      }">
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
                            <input id="birth_date" name="birth_date" type="date" required
                                value="{{ old('birth_date', $participant->birth_date?->format('Y-m-d')) }}"
                                @change="minor = isMinorCheck($event.target.value)"
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

                    {{-- Parental consent block (shown for minors) --}}
                    <div x-show="minor" x-cloak class="p-4 bg-amber-50 border border-amber-200 rounded-xl space-y-3">
                        <p class="font-semibold text-dark text-sm">
                            <i class="fas fa-child mr-2 text-amber-600"></i>Согласие от родителей / законных представителей
                        </p>
                        <p class="text-sm text-warm-gray leading-relaxed">
                            Для несовершеннолетнего участника требуется вручную заполнить и прикрепить
                            @if($consentTemplateExists)
                                <a href="{{ route('parental-consent-template') }}" target="_blank" rel="noopener noreferrer"
                                   class="text-primary underline hover:opacity-80">Согласие родителей или законных представителей на участие в конкурсах</a>
                            @else
                                <span class="font-medium text-dark">Согласие родителей или законных представителей на участие в конкурсах</span>
                            @endif
                        </p>
                        @if($hasConsent)
                            <div class="flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                                <i class="fas fa-check-circle shrink-0"></i>
                                <span>Документ уже загружен. Загрузите новый, чтобы заменить.</span>
                            </div>
                        @endif
                        <div>
                            <input type="file" name="parental_consent"
                                accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*"
                                @change="consentFile = $event.target.files[0]?.name || ''"
                                class="text-sm text-dark w-full">
                            <p class="text-xs text-warm-gray mt-1">PDF или фото документа · до 10 МБ</p>
                        </div>
                        @error('parental_consent')
                            <p class="text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="submit"
                            :disabled="minor && !hasConsent && !consentFile"
                            :class="(minor && !hasConsent && !consentFile) ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                            class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg transition-opacity">
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
