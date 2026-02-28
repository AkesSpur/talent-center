<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('contests.show', $contest) }}" class="text-warm-gray hover:text-primary transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>{{ $contest->title }}
                </a>
            </div>
            <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Редактировать конкурс</h2>
            <p class="text-warm-gray mt-1">{{ $contest->organization->name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                @php
                    $initialCategories = old('categories') ?? $contest->categories->map(fn($c) => ['name' => $c->name, 'description' => $c->description ?? ''])->toArray();
                @endphp
                <form method="POST" action="{{ route('contests.update', $contest) }}"
                      enctype="multipart/form-data"
                      x-data="contestForm({{ \Illuminate\Support\Js::from($initialCategories) }}, {{ \Illuminate\Support\Js::from($orgsData) }}, {{ $contest->organization_id }}, {{ \Illuminate\Support\Js::from($selectedJuryIds) }})"
                      class="space-y-8">
                    @csrf
                    @method('PUT')

                    {{-- Section 0: Organization (read-only) --}}
                    <div class="space-y-3">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Организация</h3>
                        <div class="px-4 py-3 bg-cream rounded-lg border border-primary/10 text-sm text-dark font-medium">
                            {{ $contest->organization->name }}
                        </div>
                    </div>

                    {{-- Section 1: Basic Info --}}
                    <div class="space-y-5">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Основная информация</h3>

                        <div>
                            <label for="platform_category_id" class="block text-sm font-medium text-dark mb-2">Категория</label>
                            <select id="platform_category_id" name="platform_category_id"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">— Без категории —</option>
                                @foreach($platformCategories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('platform_category_id', $contest->platform_category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('platform_category_id')" />
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-dark mb-2">Название конкурса <span class="text-red-500">*</span></label>
                            <input id="title" name="title" type="text" value="{{ old('title', $contest->title) }}" required
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-dark mb-2">Описание <span class="text-red-500">*</span></label>
                            <textarea id="description" name="description" rows="5"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-none">{{ old('description', $contest->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <label for="rules" class="block text-sm font-medium text-dark mb-2">Правила участия</label>
                            <textarea id="rules" name="rules" rows="4"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-none">{{ old('rules', $contest->rules) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('rules')" />
                        </div>

                        <div>
                            <label for="regulations_url" class="block text-sm font-medium text-dark mb-2">Положение о конкурсе</label>
                            <input id="regulations_url" name="regulations_url" type="url" value="{{ old('regulations_url', $contest->regulations_url) }}"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="https://example.com/regulations.pdf" />
                            <p class="text-xs text-warm-gray mt-1">Ссылка на документ в облачном хранилище или на сайте. Необязательное поле.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('regulations_url')" />
                        </div>
                    </div>

                    {{-- Section 2: Dates --}}
                    <div class="space-y-5">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Даты</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label for="applications_start_at" class="block text-sm font-medium text-dark mb-2">Начало приёма заявок <span class="text-red-500">*</span></label>
                                <input id="applications_start_at" name="applications_start_at" type="date"
                                    value="{{ old('applications_start_at', $contest->applications_start_at->format('Y-m-d')) }}" required
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('applications_start_at')" />
                            </div>
                            <div>
                                <label for="applications_end_at" class="block text-sm font-medium text-dark mb-2">Окончание приёма заявок <span class="text-red-500">*</span></label>
                                <input id="applications_end_at" name="applications_end_at" type="date"
                                    value="{{ old('applications_end_at', $contest->applications_end_at->format('Y-m-d')) }}" required
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('applications_end_at')" />
                            </div>
                            <div>
                                <label for="evaluation_end_at" class="block text-sm font-medium text-dark mb-2">Дата публикации результатов <span class="text-red-500">*</span></label>
                                <input id="evaluation_end_at" name="evaluation_end_at" type="date"
                                    value="{{ old('evaluation_end_at', $contest->evaluation_end_at->format('Y-m-d')) }}" required
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('evaluation_end_at')" />
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Jury --}}
                    <div class="space-y-4" x-show="currentReps.length > 0" x-cloak>
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Члены жюри</h3>
                        <p class="text-xs text-warm-gray">Выберите представителей организации, которые будут оценивать работы.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="rep in currentReps" :key="rep.id">
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-primary/10 hover:border-primary/30 hover:bg-cream/50 cursor-pointer transition-colors"
                                    :class="selectedJuries.includes(rep.id) ? 'bg-primary/5 border-primary/30' : ''">
                                    <input type="checkbox" :value="rep.id"
                                        :checked="selectedJuries.includes(rep.id)"
                                        @change="toggleJury(rep.id)"
                                        class="rounded border-primary/30 text-primary">
                                    <span class="text-sm font-medium text-dark" x-text="rep.name"></span>
                                </label>
                            </template>
                        </div>
                        <template x-for="juryId in selectedJuries" :key="juryId">
                            <input type="hidden" name="juries[]" :value="juryId">
                        </template>
                    </div>

                    {{-- Section 4: Contest Categories (Номинации) --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-gold/20 pb-2">
                            <div>
                                <h3 class="font-serif text-lg font-semibold text-dark">Номинации</h3>
                                <p class="text-xs text-warm-gray mt-0.5">Необязательно.</p>
                            </div>
                            <button type="button" @click="addCategory()"
                                class="text-sm text-primary hover:text-primary-dark font-medium flex items-center gap-1 shrink-0">
                                <i class="fas fa-plus"></i> Добавить
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(cat, index) in categories" :key="index">
                                <div class="flex gap-3 items-start p-4 bg-cream rounded-lg border border-gold/10">
                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-dark mb-1">Название <span class="text-red-500">*</span></label>
                                            <input type="text" :name="'categories[' + index + '][name]'" x-model="cat.name"
                                                placeholder="Живопись"
                                                class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-dark mb-1">Описание</label>
                                            <input type="text" :name="'categories[' + index + '][description]'" x-model="cat.description"
                                                placeholder="Краткое описание номинации"
                                                class="w-full px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm" />
                                        </div>
                                    </div>
                                    <button type="button" @click="removeCategory(index)"
                                        class="text-warm-gray hover:text-red-500 mt-6 transition-colors shrink-0">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                </div>
                            </template>

                            <div x-show="categories.length === 0" class="text-center py-4 text-warm-gray text-sm italic">
                                Номинации не добавлены
                            </div>
                        </div>
                    </div>

                    {{-- Section 5: Cover Image --}}
                    <div class="space-y-3" x-data="{ coverPreview: null }">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Обложка конкурса</h3>
                        <div class="flex items-start gap-5">
                            <div class="w-32 h-24 rounded-lg border-2 border-dashed border-primary/20 flex items-center justify-center bg-cream overflow-hidden shrink-0">
                                @if($contest->cover_image)
                                    <img src="{{ asset('storage/' . $contest->cover_image) }}"
                                        class="w-full h-full object-cover"
                                        x-show="!coverPreview" />
                                @endif
                                <template x-if="coverPreview">
                                    <img :src="coverPreview" class="w-full h-full object-cover" />
                                </template>
                                @if(! $contest->cover_image)
                                    <div class="text-center text-warm-gray" x-show="!coverPreview">
                                        <i class="fas fa-image text-2xl mb-1"></i>
                                        <p class="text-xs">Предпросмотр</p>
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <label for="cover-image-input" class="inline-flex items-center gap-2 px-4 py-2 border border-primary/30 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 cursor-pointer transition-colors">
                                    <i class="fas fa-upload"></i> Загрузить обложку
                                </label>
                                <input id="cover-image-input" name="cover_image" type="file" accept="image/*" class="hidden"
                                    @change="coverPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                @if($contest->cover_image)
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" id="delete_cover" name="delete_cover_image" value="1" class="rounded border-primary/30 text-primary">
                                        <label for="delete_cover" class="text-sm text-warm-gray cursor-pointer">Удалить текущую обложку</label>
                                    </div>
                                @endif
                                <p class="text-xs text-warm-gray">JPG, PNG, WebP — до 2 МБ.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
                            </div>
                        </div>
                    </div>

                    {{-- Section 6: Diploma Background --}}
                    <div class="space-y-3" x-data="{ diplomaPreview: null }">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Фон диплома</h3>
                        <div class="flex items-start gap-5">
                            <div class="w-32 h-24 rounded-lg border-2 border-dashed border-primary/20 flex items-center justify-center bg-cream overflow-hidden shrink-0">
                                @if($contest->diploma_background)
                                    <img src="{{ asset('storage/' . $contest->diploma_background) }}"
                                        class="w-full h-full object-cover"
                                        x-show="!diplomaPreview" />
                                @endif
                                <template x-if="diplomaPreview">
                                    <img :src="diplomaPreview" class="w-full h-full object-cover" />
                                </template>
                                @if(! $contest->diploma_background)
                                    <div class="text-center text-warm-gray" x-show="!diplomaPreview">
                                        <i class="fas fa-image text-2xl mb-1"></i>
                                        <p class="text-xs">Предпросмотр</p>
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <label for="diploma-bg-input" class="inline-flex items-center gap-2 px-4 py-2 border border-primary/30 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 cursor-pointer transition-colors">
                                    <i class="fas fa-upload"></i> Загрузить новый фон
                                </label>
                                <input id="diploma-bg-input" name="diploma_background" type="file" accept="image/*" class="hidden"
                                    @change="diplomaPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                @if($contest->diploma_background)
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" id="delete_diploma" name="delete_diploma_background" value="1" class="rounded border-primary/30 text-primary">
                                        <label for="delete_diploma" class="text-sm text-warm-gray cursor-pointer">Удалить текущий фон</label>
                                    </div>
                                @endif
                                <p class="text-xs text-warm-gray">JPG, PNG, WebP — до 2 МБ.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('diploma_background')" />
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex flex-wrap gap-4" x-data>
                        <button type="submit" class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                            <i class="fas fa-save mr-2"></i>Сохранить изменения
                        </button>
                        <a href="{{ route('contests.show', $contest) }}" class="px-6 py-3 border border-primary/30 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors">
                            Отмена
                        </a>

                        @can('cancel', $contest)
                            <button type="button"
                                @click="$dispatch('confirm-cancel', { action: '{{ route('contests.cancel', $contest) }}' })"
                                class="px-6 py-3 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50 transition-colors ml-auto">
                                <i class="fas fa-ban mr-2"></i>Отменить конкурс
                            </button>
                        @endcan
                    </div>

                </form>
            </div>
        </div>
    </div>

    @can('cancel', $contest)
        <x-confirm-modal
            name="cancel"
            title="Отменить конкурс"
            :message="'Вы уверены, что хотите отменить конкурс «' . $contest->title . '»? Это действие нельзя отменить.'"
            icon="fa-ban"
            iconColor="text-red-600"
            iconBg="bg-red-100"
            confirmText="Да, отменить"
            confirmClass="bg-red-600 text-white"
        />
    @endcan

</x-app-layout>

<script>
function contestForm(initialCategories, orgsData, contestOrgId, selectedJuryIds) {
    return {
        categories: initialCategories && initialCategories.length > 0
            ? initialCategories
            : [],
        orgs: orgsData || [],
        selectedOrgId: contestOrgId,
        selectedJuries: (selectedJuryIds || []).map(Number),
        get currentReps() {
            const org = this.orgs.find(o => o.id == this.selectedOrgId);
            return org ? org.reps : [];
        },
        addCategory() {
            this.categories.push({ name: '', description: '' });
        },
        removeCategory(index) {
            this.categories.splice(index, 1);
        },
        toggleJury(id) {
            const idx = this.selectedJuries.indexOf(id);
            if (idx === -1) this.selectedJuries.push(id);
            else this.selectedJuries.splice(idx, 1);
        },
    };
}
</script>
