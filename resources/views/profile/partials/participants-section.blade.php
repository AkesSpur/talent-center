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
                <form :action="editingId ? '{{ url('participants') }}/' + editingId : '{{ route('participants.store') }}'" method="POST" class="p-6 space-y-5">
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
                        <label class="block text-sm font-medium text-dark mb-1.5">Дата рождения</label>
                        <input name="birth_date" type="date" x-model="form.birth_date"
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

                    {{-- Buttons --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeModal()"
                            class="flex-1 py-2.5 border border-primary text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors">
                            Отмена
                        </button>
                        <button type="submit"
                            class="flex-1 py-2.5 gradient-gold text-dark font-medium rounded-lg hover:opacity-90 transition-opacity">
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
        form: {
            last_name: '',
            first_name: '',
            patronymic: '',
            birth_date: '',
            organization: '',
            city: '',
            group: '',
        },
        openAddModal() {
            this.editingId = null;
            this.form = { last_name: '', first_name: '', patronymic: '', birth_date: '', organization: '', city: '', group: '' };
            this.showModal = true;
        },
        openEditModal(data) {
            this.editingId = data.id;
            this.form = {
                last_name: data.last_name,
                first_name: data.first_name,
                patronymic: data.patronymic,
                birth_date: data.birth_date,
                organization: data.organization,
                city: data.city,
                group: data.group,
            };
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.editingId = null;
        }
    }
}
</script>
