<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('dashboard.contests') }}" class="text-warm-gray hover:text-primary transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Мои конкурсы
                </a>
            </div>
            <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Создать конкурс</h2>
            <p class="text-warm-gray mt-1">Заполните информацию о конкурсе</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                @php
                    $initialCategories = old('categories') ?? [];
                @endphp
                <form method="POST" action="{{ route('contests.store') }}"
                      enctype="multipart/form-data"
                      x-data="contestForm({{ \Illuminate\Support\Js::from($initialCategories) }}, {{ \Illuminate\Support\Js::from($orgsData) }}, {{ $preselectedOrgId }})"
                      class="space-y-8">
                    @csrf

                    {{-- Section 0: Organization --}}
                    <div class="space-y-5">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Организация</h3>
                        <div>
                            <label for="organization_id" class="block text-sm font-medium text-dark mb-2">Организация <span class="text-red-500">*</span></label>
                            <select id="organization_id" name="organization_id"
                                x-model="selectedOrgId"
                                @change="selectedJuries = []"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">— Выберите организацию —</option>
                                @foreach($orgsData as $org)
                                    <option value="{{ $org['id'] }}" {{ old('organization_id', $preselectedOrgId) == $org['id'] ? 'selected' : '' }}>
                                        {{ $org['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('organization_id')" />
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
                                    <option value="{{ $cat->id }}" {{ old('platform_category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('platform_category_id')" />
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-dark mb-2">Название конкурса <span class="text-red-500">*</span></label>
                            <input id="title" name="title" type="text" value="{{ old('title') }}" required
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                placeholder="Всероссийский конкурс юных художников" />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-dark mb-2">Описание <span class="text-red-500">*</span></label>
                            <textarea id="description" name="description" rows="5"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-none"
                                placeholder="Расскажите о конкурсе: цели, задачи, кто может участвовать...">{{ old('description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <label for="rules" class="block text-sm font-medium text-dark mb-2">Правила участия</label>
                            <textarea id="rules" name="rules" rows="4"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-none"
                                placeholder="Условия подачи работ, требования к участникам, критерии оценки...">{{ old('rules') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('rules')" />
                        </div>
                    </div>

                    {{-- Section 2: Dates --}}
                    <div class="space-y-5">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Даты</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label for="applications_start_at" class="block text-sm font-medium text-dark mb-2">Начало приёма заявок <span class="text-red-500">*</span></label>
                                <input id="applications_start_at" name="applications_start_at" type="date"
                                    value="{{ old('applications_start_at') }}" required
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('applications_start_at')" />
                            </div>
                            <div>
                                <label for="applications_end_at" class="block text-sm font-medium text-dark mb-2">Окончание приёма заявок <span class="text-red-500">*</span></label>
                                <input id="applications_end_at" name="applications_end_at" type="date"
                                    value="{{ old('applications_end_at') }}" required
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('applications_end_at')" />
                            </div>
                            <div>
                                <label for="evaluation_end_at" class="block text-sm font-medium text-dark mb-2">Дата публикации результатов <span class="text-red-500">*</span></label>
                                <input id="evaluation_end_at" name="evaluation_end_at" type="date"
                                    value="{{ old('evaluation_end_at') }}" required
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('evaluation_end_at')" />
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Jury --}}
                    <div class="space-y-4" x-show="selectedOrgId && currentReps.length > 0" x-cloak>
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
                                <p class="text-xs text-warm-gray mt-0.5">Необязательно. Добавьте номинации, если конкурс разбит на категории.</p>
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
                                                placeholder="Для участников от 10 до 14 лет"
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
                                <template x-if="coverPreview">
                                    <img :src="coverPreview" class="w-full h-full object-cover" />
                                </template>
                                <template x-if="!coverPreview">
                                    <div class="text-center text-warm-gray">
                                        <i class="fas fa-image text-2xl mb-1"></i>
                                        <p class="text-xs">Предпросмотр</p>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <label for="cover-image-input" class="inline-flex items-center gap-2 px-4 py-2 border border-primary/30 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 cursor-pointer transition-colors">
                                    <i class="fas fa-upload"></i> Загрузить обложку
                                </label>
                                <input id="cover-image-input" name="cover_image" type="file" accept="image/*" class="hidden"
                                    @change="coverPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                <p class="text-xs text-warm-gray mt-2">JPG, PNG, WebP — до 2 МБ.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
                            </div>
                        </div>
                    </div>

                    {{-- Section 6: Diploma Background --}}
                    <div class="space-y-3" x-data="{ diplomaPreview: null }">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Фон диплома</h3>
                        <div class="flex items-start gap-5">
                            <div class="w-32 h-24 rounded-lg border-2 border-dashed border-primary/20 flex items-center justify-center bg-cream overflow-hidden shrink-0">
                                <template x-if="diplomaPreview">
                                    <img :src="diplomaPreview" class="w-full h-full object-cover" />
                                </template>
                                <template x-if="!diplomaPreview">
                                    <div class="text-center text-warm-gray">
                                        <i class="fas fa-image text-2xl mb-1"></i>
                                        <p class="text-xs">Предпросмотр</p>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <label for="diploma-bg-input" class="inline-flex items-center gap-2 px-4 py-2 border border-primary/30 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 cursor-pointer transition-colors">
                                    <i class="fas fa-upload"></i> Загрузить фон
                                </label>
                                <input id="diploma-bg-input" name="diploma_background" type="file" accept="image/*" class="hidden"
                                    @change="diplomaPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                <p class="text-xs text-warm-gray mt-2">JPG, PNG, WebP — до 2 МБ. Рекомендуется формат A4 (горизонтальный).</p>
                                <x-input-error class="mt-2" :messages="$errors->get('diploma_background')" />
                            </div>
                        </div>
                    </div>

                    {{-- Info note --}}
                    <div class="bg-cream rounded-lg p-4 text-sm text-warm-gray">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        Конкурс будет автоматически переведён в соответствующий статус на основе указанных дат.
                    </div>

                    {{-- Submit --}}
                    <div class="flex gap-4">
                        <button type="submit" class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                            <i class="fas fa-plus mr-2"></i>Создать конкурс
                        </button>
                        <a href="{{ route('dashboard.contests') }}" class="px-6 py-3 border border-primary/30 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors">
                            Отмена
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
function contestForm(initialCategories, orgsData, preselectedOrgId) {
    return {
        categories: initialCategories && initialCategories.length > 0
            ? initialCategories
            : [],
        orgs: orgsData || [],
        selectedOrgId: preselectedOrgId || null,
        selectedJuries: [],
        get currentReps() {
            if (!this.selectedOrgId) return [];
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
