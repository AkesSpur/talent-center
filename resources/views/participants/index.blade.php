<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Мои участники</h2>
                <p class="text-warm-gray mt-1">Управление участниками конкурсов</p>
            </div>
            @if($children->count() > 0)
                <button type="button" onclick="document.getElementById('add-participant-modal').showModal()"
                   class="self-start sm:self-auto px-4 py-2 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm shrink-0">
                    <i class="fas fa-plus mr-2"></i>Добавить участника
                </button>
            @else
                <a href="#add-participant-form"
                   onclick="event.preventDefault(); document.getElementById('add-participant-form').scrollIntoView({ behavior: 'smooth' })"
                   class="self-start sm:self-auto px-4 py-2 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm shrink-0">
                    <i class="fas fa-plus mr-2"></i>Добавить участника
                </a>
            @endif
        </div>
    </x-slot>

    {{-- Confirm Delete Participant Modal --}}
    <x-confirm-modal
        name="delete-participant"
        title="Удалить участника"
        message="Вы уверены, что хотите удалить этого участника?"
        icon="fa-user-minus"
        iconColor="text-red-600"
        iconBg="bg-red-100"
        confirmText="Удалить"
        confirmClass="bg-red-600 text-white hover:bg-red-700"
        method="DELETE"
    />

    <div x-data class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Participant List --}}
                <div class="lg:col-span-2">
                    @if($children->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($children as $child)
                                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-5">
                                    <div class="flex items-start justify-between gap-3 mb-4">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <x-user-avatar :user="$child" size="md" />
                                            <div class="min-w-0">
                                                <h3 class="font-semibold text-dark truncate">{{ $child->last_name }} {{ $child->first_name }}</h3>
                                                @if($child->patronymic)
                                                    <p class="text-sm text-warm-gray truncate">{{ $child->patronymic }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <a href="{{ route('participants.edit', $child) }}"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-primary/20 text-primary hover:bg-primary/5 transition-colors"
                                                title="Редактировать">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <button type="button"
                                                @click="$dispatch('confirm-delete-participant', { action: '{{ route('participants.destroy', $child) }}' })"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-200 text-red-500 hover:bg-red-50 transition-colors"
                                                title="Удалить">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="space-y-1.5 text-sm text-warm-gray">
                                        @if($child->birth_date)
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-birthday-cake w-4 text-center text-primary/50"></i>
                                                {{ $child->birth_date->format('d.m.Y') }}
                                            </div>
                                        @endif
                                        @if($child->organization)
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-school w-4 text-center text-primary/50"></i>
                                                <span class="truncate">{{ $child->organization }}</span>
                                            </div>
                                        @endif
                                        @if($child->city)
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-map-marker-alt w-4 text-center text-primary/50"></i>
                                                <span class="truncate">{{ $child->city }}</span>
                                            </div>
                                        @endif
                                        @if($child->group)
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-users w-4 text-center text-primary/50"></i>
                                                <span class="truncate">{{ $child->group }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-10 text-center">
                            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-users text-2xl text-primary"></i>
                            </div>
                            <h3 class="font-serif text-lg font-semibold text-dark mb-2">Нет добавленных участников</h3>
                            <p class="text-warm-gray text-sm">Добавьте участников, чтобы подавать заявки на конкурсы от их имени</p>
                        </div>
                    @endif
                </div>

                {{-- Inline Add Participant Form (only when no participants yet) --}}
                @if($children->count() === 0)
                <div id="add-participant-form" class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-serif text-lg font-semibold text-dark mb-5">
                            <i class="fas fa-user-plus text-primary mr-2"></i>Добавить участника
                        </h3>

                        @php $consentTemplateExists = \App\Models\SiteSettings::get(\App\Models\SiteSettings::PARENTAL_CONSENT_DOCUMENT); @endphp
                        <form method="POST" action="{{ route('participants.store') }}" enctype="multipart/form-data"
                              class="space-y-4"
                              x-data="{
                                  minor: false, consentFile: '',
                                  isMinorCheck(v) {
                                      if (!v) return false;
                                      const d = new Date(v), t = new Date(d);
                                      t.setFullYear(t.getFullYear() + 18); t.setDate(t.getDate() + 1);
                                      return new Date() < t;
                                  }
                              }">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-dark mb-2">Фамилия <span class="text-red-500">*</span></label>
                                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                        placeholder="Иванов" />
                                    <x-input-error class="mt-1" :messages="$errors->get('last_name')" />
                                </div>
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-dark mb-2">Имя <span class="text-red-500">*</span></label>
                                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                        placeholder="Иван" />
                                    <x-input-error class="mt-1" :messages="$errors->get('first_name')" />
                                </div>
                                <div>
                                    <label for="patronymic" class="block text-sm font-medium text-dark mb-2">Отчество</label>
                                    <input id="patronymic" name="patronymic" type="text" value="{{ old('patronymic') }}"
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                        placeholder="Иванович" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="birth_date" class="block text-sm font-medium text-dark mb-2">Дата рождения <span class="text-red-500">*</span></label>
                                    <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" required
                                        @change="minor = isMinorCheck($event.target.value)"
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                    <x-input-error class="mt-1" :messages="$errors->get('birth_date')" />
                                </div>
                                <div>
                                    <label for="city" class="block text-sm font-medium text-dark mb-2">Город</label>
                                    <input id="city" name="city" type="text" value="{{ old('city') }}"
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                        placeholder="Москва" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="organization" class="block text-sm font-medium text-dark mb-2">Учреждение</label>
                                    <input id="organization" name="organization" type="text" value="{{ old('organization') }}"
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                        placeholder="ДШИ №1" />
                                </div>
                                <div>
                                    <label for="group" class="block text-sm font-medium text-dark mb-2">Класс / группа</label>
                                    <input id="group" name="group" type="text" value="{{ old('group') }}"
                                        class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                        placeholder="3А" />
                                </div>
                            </div>

                            {{-- Parental consent block --}}
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

                            <div class="pt-2">
                                <button type="submit"
                                    :disabled="minor && !consentFile"
                                    :class="(minor && !consentFile) ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                                    class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg transition-opacity">
                                    <i class="fas fa-plus mr-2"></i>Добавить участника
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Add Participant Modal (when participants already exist) --}}
    @if($children->count() > 0)
    <dialog id="add-participant-modal" class="bg-transparent p-0 m-0 max-w-none w-full h-full max-h-none backdrop:bg-dark/40 backdrop:backdrop-blur-sm">
        <div class="fixed inset-0 flex items-center justify-center p-4" onclick="if(event.target===this) document.getElementById('add-participant-modal').close()">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                {{-- Modal Header --}}
                <div class="sticky top-0 bg-white border-b border-primary/10 px-6 py-4 flex items-center justify-between rounded-t-xl z-10">
                    <h3 class="font-serif text-xl font-semibold text-dark">
                        <i class="fas fa-user-plus text-primary mr-2"></i>Новый участник
                    </h3>
                    <button type="button" onclick="document.getElementById('add-participant-modal').close()" class="text-warm-gray hover:text-dark transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" action="{{ route('participants.store') }}"
                      enctype="multipart/form-data"
                      class="p-6 space-y-4"
                      x-data="{
                          minor: false,
                          consentFile: '',
                          isMinorCheck(v) {
                              if (!v) return false;
                              const d = new Date(v), t = new Date(d);
                              t.setFullYear(t.getFullYear() + 18); t.setDate(t.getDate() + 1);
                              return new Date() < t;
                          }
                      }">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Фамилия <span class="text-red-500">*</span></label>
                            <input name="last_name" type="text" value="{{ old('last_name') }}" required
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="Иванов" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Имя <span class="text-red-500">*</span></label>
                            <input name="first_name" type="text" value="{{ old('first_name') }}" required
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="Иван" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Отчество</label>
                            <input name="patronymic" type="text" value="{{ old('patronymic') }}"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="Иванович" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Дата рождения <span class="text-red-500">*</span></label>
                            <input name="birth_date" type="date" value="{{ old('birth_date') }}" required
                                @change="minor = isMinorCheck($event.target.value)"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Город</label>
                            <input name="city" type="text" value="{{ old('city') }}"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="Москва" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Учреждение</label>
                            <input name="organization" type="text" value="{{ old('organization') }}"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="ДШИ №1" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Класс / группа</label>
                            <input name="group" type="text" value="{{ old('group') }}"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="3А" />
                        </div>
                    </div>

                    {{-- Parental consent block --}}
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

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('add-participant-modal').close()"
                            class="flex-1 py-2.5 border border-primary text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                            :disabled="minor && !consentFile"
                            :class="(minor && !consentFile) ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                            class="flex-1 py-2.5 gradient-gold text-dark font-medium rounded-lg transition-opacity">
                            <i class="fas fa-plus mr-2"></i>Добавить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('add-participant-modal').showModal();
        });
    </script>
    @endif
    @endif
</x-app-layout>
