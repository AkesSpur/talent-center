<div x-data="participantsManager()" class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h3 class="font-serif text-lg font-semibold text-dark">Мои участники</h3>
                <p class="text-warm-gray text-sm mt-1">Управление детьми и подопечными для участия в конкурсах</p>
            </div>
            <button @click="openAddModal()" class="self-start sm:self-auto px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i>Добавить
            </button>
        </div>

        {{-- Participants List --}}
        @if($participants->count() > 0)
            <div class="space-y-3">
                @foreach($participants as $participant)
                    <div class="flex items-center gap-4 p-4 border border-primary/10 rounded-lg hover:bg-cream/50 transition-colors">
                        <div class="w-12 h-12 rounded-full gradient-gold flex items-center justify-center text-white font-semibold text-lg flex-shrink-0">
                            {{ mb_substr($participant->last_name, 0, 1) }}{{ mb_substr($participant->first_name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-dark">
                                {{ $participant->last_name }} {{ $participant->first_name }} {{ $participant->patronymic }}
                            </p>
                            <p class="text-sm text-warm-gray">
                                @if($participant->birth_date) {{ $participant->birth_date->age }} лет @endif
                                @if($participant->organization) &bull; {{ $participant->organization }} @endif
                                @if($participant->group) &bull; {{ $participant->group }} @endif
                                @if($participant->city) &bull; {{ $participant->city }} @endif
                            </p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button
                                @click="openEditModal({{ json_encode([
                                    'id' => $participant->id,
                                    'last_name' => $participant->last_name,
                                    'first_name' => $participant->first_name,
                                    'patronymic' => $participant->patronymic ?? '',
                                    'birth_date' => $participant->birth_date?->format('Y-m-d') ?? '',
                                    'organization' => $participant->organization ?? '',
                                    'city' => $participant->city ?? '',
                                    'group' => $participant->group ?? '',
                                    'has_consent' => (bool) $participant->parental_consent_path,
                                ]) }})"
                                class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors"
                                title="Редактировать">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('participants.destroy', $participant) }}"
                                onsubmit="return confirm('Удалить участника {{ addslashes($participant->first_name) }} {{ addslashes($participant->last_name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-2xl text-primary"></i>
                </div>
                <p class="text-dark font-medium mb-2">У вас пока нет добавленных участников</p>
                <p class="text-warm-gray text-sm mb-4">Добавьте детей или подопечных для участия в конкурсах</p>
                <button @click="openAddModal()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    <i class="fas fa-plus mr-2"></i>Добавить участника
                </button>
            </div>
        @endif
    </div>

    {{-- Add/Edit Participant Modal --}}
    <template x-teleport="body">
        <div x-show="showModal" x-cloak
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
            style="z-index: 9999;"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div @click.outside="closeModal()" class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                {{-- Modal Header --}}
                <div class="sticky top-0 bg-white border-b border-primary/10 px-6 py-4 flex items-center justify-between rounded-t-xl">
                    <h3 class="font-serif text-xl font-semibold text-dark" x-text="editingId ? 'Редактировать участника' : 'Новый участник'"></h3>
                    <button @click="closeModal()" class="text-warm-gray hover:text-dark transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form :action="editingId ? '{{ url('participants') }}/' + editingId : '{{ route('participants.store') }}'" method="POST"
                      enctype="multipart/form-data"
                      class="p-6 space-y-5">
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- ФИО Row --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Фамилия <span class="text-red-500">*</span></label>
                            <input name="last_name" type="text" required x-model="form.last_name"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Имя <span class="text-red-500">*</span></label>
                            <input name="first_name" type="text" required x-model="form.first_name"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Отчество</label>
                            <input name="patronymic" type="text" x-model="form.patronymic"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                    </div>

                    {{-- Birth Date --}}
                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Дата рождения <span class="text-red-500">*</span></label>
                        <input name="birth_date" type="date" required x-model="form.birth_date"
                            @change="form.birth_date = $event.target.value; minor = isMinorCheck($event.target.value)"
                            class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                    </div>

                    {{-- Organization --}}
                    <div>
                        <label class="block text-sm font-medium text-dark mb-1.5">Организация</label>
                        <input name="organization" type="text" x-model="form.organization" placeholder="Школа, студия, кружок..."
                            class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                    </div>

                    {{-- City + Group Row --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Город</label>
                            <input name="city" type="text" x-model="form.city"
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark mb-1.5">Курс/класс/группа</label>
                            <input name="group" type="text" x-model="form.group" placeholder="5А, 10 класс..."
                                class="w-full px-3 py-2.5 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                        </div>
                    </div>

                    {{-- Parental consent block --}}
                    @php $consentTemplateExists = \App\Models\SiteSettings::get(\App\Models\SiteSettings::PARENTAL_CONSENT_DOCUMENT); @endphp
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
                        <div x-show="hasConsent" class="flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                            <i class="fas fa-check-circle shrink-0"></i>
                            <span>Документ уже загружен. Загрузите новый, чтобы заменить.</span>
                        </div>
                        <div>
                            <input type="file" name="parental_consent"
                                accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*"
                                @change="consentFile = $event.target.files[0]?.name || ''"
                                class="text-sm text-dark w-full">
                            <p class="text-xs text-warm-gray mt-1">PDF или фото документа · до 10 МБ</p>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal()"
                            class="flex-1 py-2.5 border border-primary text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                            :disabled="minor && !hasConsent && !consentFile"
                            :class="(minor && !hasConsent && !consentFile) ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                            class="flex-1 py-2.5 gradient-gold text-dark font-medium rounded-lg transition-opacity">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
function participantsManager() {
    return {
        showModal: false,
        editingId: null,
        minor: false,
        hasConsent: false,
        consentFile: '',
        form: {
            last_name: '',
            first_name: '',
            patronymic: '',
            birth_date: '',
            organization: '',
            city: '',
            group: '',
        },
        isMinorCheck(v) {
            if (!v) return false;
            const d = new Date(v), t = new Date(d);
            t.setFullYear(t.getFullYear() + 18); t.setDate(t.getDate() + 1);
            return new Date() < t;
        },
        openAddModal() {
            this.editingId = null;
            this.minor = false;
            this.hasConsent = false;
            this.consentFile = '';
            this.form = { last_name: '', first_name: '', patronymic: '', birth_date: '', organization: '', city: '', group: '' };
            this.showModal = true;
        },
        openEditModal(data) {
            this.editingId = data.id;
            this.hasConsent = data.has_consent ?? false;
            this.consentFile = '';
            this.form = {
                last_name: data.last_name,
                first_name: data.first_name,
                patronymic: data.patronymic,
                birth_date: data.birth_date,
                organization: data.organization,
                city: data.city,
                group: data.group,
            };
            this.minor = this.isMinorCheck(data.birth_date);
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.editingId = null;
        }
    }
}
</script>
