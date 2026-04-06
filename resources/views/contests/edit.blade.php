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
                    $initialCategories = old('categories') ?? $contest->categories->map(fn($c) => [
                        'name' => $c->name,
                        'description' => $c->description ?? '',
                        'age_groups' => $c->ageGroups->map(fn($ag) => [
                            'name' => $ag->name,
                            'min_age' => $ag->min_age,
                            'max_age' => $ag->max_age,
                        ])->toArray(),
                    ])->toArray();
                    $initialContestAgeGroups = old('contest_age_groups') ?? $contest->contestLevelAgeGroups->map(fn($ag) => [
                        'name' => $ag->name,
                        'min_age' => $ag->min_age,
                        'max_age' => $ag->max_age,
                    ])->toArray();
                    $restoredJuryIds = old('juries') ? array_map('intval', old('juries')) : $selectedJuryIds;
                    $restoredGenreId = old('platform_category_id', $contest->platform_category_id);
                    $restoredDiplomaBg = old('selected_diploma_background_path', $contest->diploma_background);
                    $restoredCoverPath = old('selected_cover_path', '');
                @endphp
                <form id="contest-edit-form" method="POST" action="{{ route('contests.update', $contest) }}"
                      enctype="multipart/form-data"
                      x-data="contestForm({{ \Illuminate\Support\Js::from($initialCategories) }}, {{ \Illuminate\Support\Js::from($orgsData) }}, {{ $contest->organization_id }}, {{ \Illuminate\Support\Js::from($restoredJuryIds) }}, {{ \Illuminate\Support\Js::from($initialContestAgeGroups) }}, {{ \Illuminate\Support\Js::from($diplomaBackgrounds) }}, {{ \Illuminate\Support\Js::from($restoredGenreId) }}, {{ \Illuminate\Support\Js::from($restoredDiplomaBg) }}, {{ \Illuminate\Support\Js::from($contestCovers) }}, {{ \Illuminate\Support\Js::from($restoredCoverPath) }})"
                      class="space-y-8">
                    @csrf
                    @method('PUT')

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-sm text-red-600 font-medium">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Пожалуйста, исправьте ошибки в форме.
                            </p>
                        </div>
                    @endif

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
                            <label for="platform_category_id" class="block text-sm font-medium text-dark mb-2">Жанр</label>
                            <select id="platform_category_id" name="platform_category_id" x-model="selectedGenreId"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                                <option value="">— Без жанра —</option>
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
                            <label class="block text-sm font-medium text-dark mb-2">Описание <span class="text-red-500">*</span></label>
                            <div id="description-editor" class="ql-contest-editor"></div>
                            <input type="hidden" name="description" id="description-input">
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-dark mb-2">Правила участия</label>
                            <div id="rules-editor" class="ql-contest-editor"></div>
                            <input type="hidden" name="rules" id="rules-input">
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

                        {{-- Paid contest toggle + entry fee + application limit --}}
                        <div
                            x-data="{
                                isPaid: {{ old('is_paid', $contest->is_paid) ? 'true' : 'false' }},
                                entryFee: '{{ old('entry_fee', $contest->entry_fee) }}',
                                applicationLimit: '{{ old('application_limit', $contest->application_limit) }}',
                                orgCanHostPaid: {{ $contest->organization->canHostPaidContests() ? 'true' : 'false' }}
                            }"
                            class="p-4 border border-primary/15 rounded-xl bg-cream/30 space-y-4"
                        >
                            {{-- Row: checkbox | entry fee | limit --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">

                                {{-- Paid checkbox --}}
                                <div>
                                    <p class="block text-sm font-medium text-dark mb-2">Тип конкурса</p>
                                    <label class="inline-flex items-center gap-2 cursor-pointer select-none"
                                        :class="{ 'opacity-50 cursor-not-allowed': !orgCanHostPaid }">
                                        <input type="checkbox" name="is_paid" value="1"
                                            x-model="isPaid"
                                            :disabled="!orgCanHostPaid"
                                            class="rounded border-primary/30 text-primary focus:ring-primary/30 w-4 h-4">
                                        <span class="text-sm font-medium text-dark">Платный конкурс</span>
                                    </label>
                                    <div x-show="!orgCanHostPaid" class="mt-1.5 text-xs text-warm-gray leading-snug">
                                        Заполните реквизиты организации и примите оферту.
                                        <a href="{{ route('organizations.edit', $contest->organization) }}" class="text-primary underline">Редактировать организацию</a>.
                                    </div>
                                </div>

                                {{-- Entry fee (visible when paid) --}}
                                <div x-show="isPaid" x-cloak>
                                    <label for="entry_fee" class="block text-sm font-medium text-dark mb-2">
                                        Сумма оргвзноса, ₽ <span class="text-red-500">*</span>
                                    </label>
                                    <input id="entry_fee" name="entry_fee" type="number"
                                        x-model="entryFee"
                                        min="100" max="100000" step="1"
                                        class="no-spinner w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary bg-white"
                                        placeholder="500">
                                    <p class="text-xs text-warm-gray mt-1">От 100 до 100 000 ₽, без копеек.</p>
                                    @error('entry_fee')
                                        <p class="mt-1 text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>
                                {{-- Spacer when not paid --}}
                                <div x-show="!isPaid"></div>

                                {{-- Application limit --}}
                                <div>
                                    <label for="application_limit" class="block text-sm font-medium text-dark mb-2">Лимит заявок</label>
                                    <input id="application_limit" name="application_limit" type="number"
                                        x-model="applicationLimit"
                                        :min="isPaid ? 0 : 1"
                                        :max="isPaid ? 10000 : 50"
                                        class="no-spinner w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 bg-white text-sm">
                                    <p class="text-xs text-warm-gray mt-1" x-show="!isPaid">1–50 заявок</p>
                                    <p class="text-xs text-warm-gray mt-1" x-show="isPaid">0 = без ограничений</p>
                                    <x-input-error class="mt-1" :messages="$errors->get('application_limit')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Dates --}}
                    <div class="space-y-5" x-data="{ isPermanent: {{ old('is_permanent', $contest->is_permanent) ? 'true' : 'false' }} }">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Даты</h3>

                        {{-- Permanent toggle --}}
                        <label class="flex items-center gap-3 cursor-pointer select-none w-fit">
                            <input type="checkbox" name="is_permanent" value="1"
                                x-model="isPermanent"
                                {{ old('is_permanent', $contest->is_permanent) ? 'checked' : '' }}
                                class="rounded border-primary/30 text-primary focus:ring-primary/30">
                            <span class="text-sm font-medium text-dark">Бессрочный конкурс</span>
                            <span class="text-xs text-warm-gray">(без даты окончания приёма заявок)</span>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label for="applications_start_at" class="block text-sm font-medium text-dark mb-2">Начало приёма заявок <span class="text-red-500">*</span></label>
                                <input id="applications_start_at" name="applications_start_at" type="date"
                                    value="{{ old('applications_start_at', $contest->applications_start_at->format('Y-m-d')) }}" required
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('applications_start_at')" />
                            </div>
                            <div x-show="!isPermanent" x-cloak>
                                <label for="applications_end_at" class="block text-sm font-medium text-dark mb-2">Окончание приёма заявок <span class="text-red-500">*</span></label>
                                <input id="applications_end_at" name="applications_end_at" type="date"
                                    value="{{ old('applications_end_at', $contest->applications_end_at?->format('Y-m-d')) }}"
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('applications_end_at')" />
                            </div>
                            <div x-show="!isPermanent" x-cloak>
                                <label for="evaluation_end_at" class="block text-sm font-medium text-dark mb-2">Дата рассылки результатов <span class="text-red-500">*</span></label>
                                <input id="evaluation_end_at" name="evaluation_end_at" type="date"
                                    value="{{ old('evaluation_end_at', $contest->evaluation_end_at?->format('Y-m-d')) }}"
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />
                                <x-input-error class="mt-2" :messages="$errors->get('evaluation_end_at')" />
                            </div>
                        </div>

                        <p class="text-xs text-warm-gray">
                            <template x-if="!isPermanent">
                                <span><i class="fas fa-info-circle mr-1 text-primary"></i>Конкурс автоматически изменит статус в соответствии с указанными датами.</span>
                            </template>
                            <template x-if="isPermanent">
                                <span><i class="fas fa-infinity mr-1 text-primary"></i>Бессрочный конкурс остаётся открытым для приёма заявок, а жюри может оценивать работы в любой момент.</span>
                            </template>
                        </p>
                    </div>

                    {{-- Section 3: Jury --}}
                    <div class="space-y-4">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Члены жюри</h3>
                        <p class="text-xs text-warm-gray">Выберите представителей организации, которые будут оценивать работы.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="currentReps.length > 0">
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
                        <div x-show="currentReps.length === 0" class="text-sm text-warm-gray italic">
                            У организации пока нет представителей.
                        </div>
                        <template x-for="juryId in selectedJuries" :key="juryId">
                            <input type="hidden" name="juries[]" :value="juryId">
                        </template>

                        {{-- Invite jury member --}}
                        <div x-show="currentOrgCanManage" class="mt-4 p-4 bg-cream rounded-lg border border-gold/10">
                            <p class="text-sm font-medium text-dark mb-3">
                                <i class="fas fa-user-plus mr-1 text-primary"></i>Пригласить члена жюри
                            </p>
                            <div class="flex gap-2">
                                <input type="email" x-model="juryInviteEmail"
                                    placeholder="Email пользователя"
                                    class="flex-1 px-4 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm" />
                                <button type="button" @click="inviteJuryMember()"
                                    :disabled="juryInviteLoading"
                                    class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50">
                                    <i class="fas fa-plus mr-1" x-show="!juryInviteLoading"></i>
                                    <i class="fas fa-spinner fa-spin mr-1" x-show="juryInviteLoading"></i>
                                    Добавить
                                </button>
                            </div>
                            <p x-show="juryInviteError" x-text="juryInviteError" class="text-sm text-red-600 mt-2"></p>
                            <p x-show="juryInviteSuccess" x-text="juryInviteSuccess" class="text-sm text-green-600 mt-2"></p>

                            {{-- User not found: invite to register --}}
                            <div x-show="juryInviteNotFound" x-cloak
                                 class="mt-3 p-3 rounded-lg border border-amber-200 bg-amber-50">
                                <p class="text-sm text-amber-800 mb-1">
                                    <i class="fas fa-user-slash mr-1.5 text-amber-500"></i>
                                    Пользователь <strong x-text="juryInviteNotFoundEmail"></strong> не зарегистрирован.
                                </p>
                                <p class="text-xs text-amber-700 mb-3">Отправить приглашение на регистрацию?</p>
                                <div class="flex gap-2 flex-wrap">
                                    <button type="button" @click="sendJuryInvitation()"
                                        :disabled="jurySendingInvitation"
                                        class="px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded-lg hover:bg-amber-700 transition-colors disabled:opacity-50">
                                        <i class="fas fa-envelope mr-1" x-show="!jurySendingInvitation"></i>
                                        <i class="fas fa-spinner fa-spin mr-1" x-show="jurySendingInvitation"></i>
                                        Отправить приглашение
                                    </button>
                                    <button type="button"
                                        @click="juryInviteNotFound = false; juryInviteEmail = ''"
                                        class="px-3 py-1.5 border border-amber-300 text-amber-700 text-xs rounded-lg hover:bg-amber-100 transition-colors">
                                        Отмена
                                    </button>
                                </div>
                            </div>

                            <p x-show="juryInviteSent" x-cloak class="text-sm text-green-600 mt-2">
                                <i class="fas fa-check-circle mr-1"></i>
                                Приглашение отправлено на <span x-text="juryInviteNotFoundEmail"></span>
                            </p>
                        </div>
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
                                <div class="p-4 bg-cream rounded-lg border border-gold/10">
                                    <div class="flex gap-3 items-start">
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
                                    {{-- Age groups for this nomination --}}
                                    <div class="mt-3 pt-3 border-t border-gold/10">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-medium text-warm-gray">Возрастные группы / классы / курсы</span>
                                            <button type="button" @click="addAgeGroup(cat)"
                                                class="text-xs text-primary hover:text-primary-dark font-medium">
                                                <i class="fas fa-plus mr-0.5"></i>Добавить
                                            </button>
                                        </div>
                                        <template x-for="(ag, agIdx) in cat.age_groups" :key="agIdx">
                                            <div class="flex gap-2 items-center mb-2">
                                                <input type="text" :name="'categories[' + index + '][age_groups][' + agIdx + '][name]'" x-model="ag.name"
                                                    placeholder="Название группы"
                                                    class="flex-1 px-2 py-1.5 border border-primary/20 rounded text-xs focus:outline-none focus:ring-1 focus:ring-primary/30" />
                                                <button type="button" @click="cat.age_groups.splice(agIdx, 1)"
                                                    class="text-warm-gray hover:text-red-500 transition-colors">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </div>
                                        </template>
                                        <div x-show="cat.age_groups.length === 0" class="text-xs text-warm-gray italic">Не указаны</div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="categories.length === 0" class="text-center py-4 text-warm-gray text-sm italic">
                                Номинации не добавлены
                            </div>
                        </div>
                    </div>

                    {{-- Section 4b: Contest-level Age Groups (when no nominations) --}}
                    <div class="space-y-4" x-show="categories.length === 0">
                        <div class="flex items-center justify-between border-b border-gold/20 pb-2">
                            <div>
                                <h3 class="font-serif text-lg font-semibold text-dark">Возрастные группы / классы / курсы</h3>
                                <p class="text-xs text-warm-gray mt-0.5">Необязательно. Укажите возрастные группы для конкурса.</p>
                            </div>
                            <button type="button" @click="addContestAgeGroup()"
                                class="text-sm text-primary hover:text-primary-dark font-medium flex items-center gap-1 shrink-0">
                                <i class="fas fa-plus"></i> Добавить
                            </button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(ag, agIdx) in contestAgeGroups" :key="agIdx">
                                <div class="flex gap-2 items-center p-3 bg-cream rounded-lg border border-gold/10">
                                    <input type="text" :name="'contest_age_groups[' + agIdx + '][name]'" x-model="ag.name"
                                        placeholder="Название группы"
                                        class="flex-1 px-3 py-2 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                                    <button type="button" @click="contestAgeGroups.splice(agIdx, 1)"
                                        class="text-warm-gray hover:text-red-500 transition-colors shrink-0">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                </div>
                            </template>
                            <div x-show="contestAgeGroups.length === 0" class="text-center py-4 text-warm-gray text-sm italic">
                                Возрастные группы не добавлены
                            </div>
                        </div>
                    </div>

                    {{-- Section 5: Cover Image --}}
                    <div class="space-y-3">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Обложка конкурса</h3>

                        {{-- Current cover (if exists and no gallery selection / upload preview) --}}
                        @if($contest->cover_image)
                            <div x-show="!selectedCoverPath && !coverPreview" class="flex items-center gap-3 p-3 bg-cream rounded-lg border border-gold/10">
                                <img src="{{ asset('storage/' . $contest->cover_image) }}" class="w-20 h-15 object-cover rounded-lg shrink-0" style="height:60px" alt="" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-dark font-medium">Текущая обложка</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <input type="checkbox" id="delete_cover" name="delete_cover_image" value="1" class="rounded border-primary/30 text-primary">
                                        <label for="delete_cover" class="text-sm text-warm-gray cursor-pointer">Удалить</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Gallery of admin covers --}}
                        <div x-show="filteredContestCovers.length > 0" class="space-y-2">
                            <p class="text-xs text-warm-gray">Выберите из готовых обложек или загрузите свою:</p>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                <template x-for="cover in filteredContestCovers" :key="cover.id">
                                    <div class="relative cursor-pointer rounded-lg overflow-hidden border-2 transition-all aspect-[4/3]"
                                         :class="selectedCoverPath === cover.image_path ? 'border-primary ring-2 ring-primary/30' : 'border-primary/10 hover:border-primary/30'"
                                         @click="selectCoverFromGallery(cover)">
                                        <img :src="cover.image_url" class="w-full h-full object-cover" />
                                        <div x-show="selectedCoverPath === cover.image_path"
                                             class="absolute top-1 right-1 w-5 h-5 bg-primary rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                        <button type="button" @click.stop="fullscreenUrl = cover.image_url"
                                            class="absolute bottom-1 right-1 w-6 h-6 bg-black/50 rounded flex items-center justify-center text-white hover:bg-black/70 transition-colors">
                                            <i class="fas fa-expand text-xs"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <input type="hidden" name="selected_cover_path" :value="selectedCoverPath || ''">
                        </div>

                        {{-- Custom upload --}}
                        <div class="flex items-start gap-5" :class="filteredContestCovers.length > 0 ? 'pt-3 border-t border-gold/10' : ''">
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
                                    <i class="fas fa-upload"></i> Загрузить свою обложку
                                </label>
                                <input id="cover-image-input" name="cover_image" type="file" accept="image/*" class="hidden"
                                    @change="onCoverUpload($event)">
                                <p class="text-xs text-warm-gray mt-2">JPG, PNG, WebP — до 2 МБ.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
                            </div>
                        </div>
                    </div>

                    {{-- Section 6: Diploma Background --}}
                    <div class="space-y-3">
                        <h3 class="font-serif text-lg font-semibold text-dark border-b border-gold/20 pb-2">Фон диплома</h3>

                        {{-- Default backgrounds gallery --}}
                        <div x-show="filteredBackgrounds.length > 0" class="space-y-2">
                            <p class="text-xs text-warm-gray">Выберите из готовых фонов или загрузите свой:</p>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                <template x-for="bg in filteredBackgrounds" :key="bg.id">
                                    <div class="relative cursor-pointer rounded-lg overflow-hidden border-2 transition-all aspect-[297/210]"
                                         :class="selectedDefaultBg === bg.image_path ? 'border-primary ring-2 ring-primary/30' : 'border-primary/10 hover:border-primary/30'"
                                         @click="selectDefaultBg(bg)">
                                        <img :src="bg.image_url" class="w-full h-full object-cover" />
                                        <div x-show="selectedDefaultBg === bg.image_path"
                                             class="absolute top-1 right-1 w-5 h-5 bg-primary rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                        <button type="button" @click.stop="fullscreenUrl = bg.image_url"
                                            class="absolute bottom-1 right-1 w-6 h-6 bg-black/50 rounded flex items-center justify-center text-white hover:bg-black/70 transition-colors">
                                            <i class="fas fa-expand text-xs"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <input type="hidden" name="selected_diploma_background_path" :value="selectedDefaultBg || ''">
                        </div>

                        {{-- Custom upload (disabled for now — using default backgrounds only)
                        <div class="flex items-start gap-5" :class="filteredBackgrounds.length > 0 ? 'pt-3 border-t border-gold/10' : ''">
                            <div class="w-32 h-24 rounded-lg border-2 border-dashed border-primary/20 flex items-center justify-center bg-cream overflow-hidden shrink-0">
                                @if($contest->diploma_background)
                                    <img src="{{ asset('storage/' . $contest->diploma_background) }}"
                                        class="w-full h-full object-cover"
                                        x-show="!diplomaPreview && !selectedDefaultBg" />
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
                                    <i class="fas fa-upload"></i> Загрузить свой фон
                                </label>
                                <input id="diploma-bg-input" name="diploma_background" type="file" accept="image/*" class="hidden"
                                    @change="onCustomDiplomaUpload($event)">
                                @if($contest->diploma_background)
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" id="delete_diploma" name="delete_diploma_background" value="1" class="rounded border-primary/30 text-primary">
                                        <label for="delete_diploma" class="text-sm text-warm-gray cursor-pointer">Удалить текущий фон</label>
                                    </div>
                                @endif
                                <p class="text-xs text-warm-gray">JPG, PNG, WebP — до 2 МБ. Рекомендуется формат A4 (горизонтальный).</p>
                                <x-input-error class="mt-2" :messages="$errors->get('diploma_background')" />
                            </div>
                        </div>
                        --}}
                    </div>

                    {{-- Fullscreen preview overlay --}}
                    <div x-show="fullscreenUrl" x-cloak
                         @click="fullscreenUrl = null"
                         @keydown.escape.window="fullscreenUrl = null"
                         class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 cursor-pointer">
                        <button type="button" @click="fullscreenUrl = null" class="absolute top-4 right-4 text-white text-2xl hover:text-gold z-10">
                            <i class="fas fa-times"></i>
                        </button>
                        <img :src="fullscreenUrl" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" @click.stop />
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

    @push('styles')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <style>
        .ql-toolbar.ql-snow { border: 1px solid rgba(139,69,19,.2); border-bottom: none; border-radius: .5rem .5rem 0 0; background: #FAF8F5; }
        .ql-container.ql-snow { border: 1px solid rgba(139,69,19,.2); border-radius: 0 0 .5rem .5rem; font-family: 'Inter', sans-serif; font-size: .875rem; }
        .ql-editor { min-height: 130px; color: #2C2416; }
        .ql-editor.ql-blank::before { color: #9A8B7A; font-style: normal; }
        .ql-toolbar.ql-snow .ql-formats button:hover .ql-stroke, .ql-toolbar.ql-snow .ql-formats button.ql-active .ql-stroke { stroke: #8B4513; }
        .ql-toolbar.ql-snow .ql-formats button:hover .ql-fill, .ql-toolbar.ql-snow .ql-formats button.ql-active .ql-fill { fill: #8B4513; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
    (function () {
        var toolbarOptions = [
            [{ header: [2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ align: [] }],
            ['link'],
            ['clean']
        ];
        var descEditor = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Расскажите о конкурсе: цели, задачи, кто может участвовать...',
            modules: { toolbar: toolbarOptions }
        });
        var rulesEditor = new Quill('#rules-editor', {
            theme: 'snow',
            placeholder: 'Условия подачи работ, требования к участникам, критерии оценки...',
            modules: { toolbar: toolbarOptions }
        });
        var initDesc = @json(old('description', $contest->description ?? ''));
        var initRules = @json(old('rules', $contest->rules ?? ''));
        if (initDesc) descEditor.clipboard.dangerouslyPasteHTML(initDesc);
        if (initRules) rulesEditor.clipboard.dangerouslyPasteHTML(initRules);
        document.getElementById('contest-edit-form').addEventListener('submit', function () {
            document.getElementById('description-input').value = descEditor.root.innerHTML;
            var rulesHtml = rulesEditor.root.innerHTML;
            document.getElementById('rules-input').value = (rulesHtml === '<p><br></p>') ? '' : rulesHtml;
        });
    })();
    </script>
    @endpush

</x-app-layout>

<script>
function contestForm(initialCategories, orgsData, contestOrgId, selectedJuryIds, initialContestAgeGroups, diplomaBgData, initialGenreId, initialDiplomaBg, contestCoverData, initialCoverPath) {
    return {
        categories: (initialCategories && (Array.isArray(initialCategories) ? initialCategories.length > 0 : Object.keys(initialCategories).length > 0)
            ? Object.values(initialCategories)
            : []).map(c => ({ ...c, age_groups: c.age_groups ? Object.values(c.age_groups) : [] })),
        contestAgeGroups: initialContestAgeGroups ? Object.values(initialContestAgeGroups) : [],
        orgs: orgsData || [],
        selectedOrgId: contestOrgId,
        selectedJuries: (selectedJuryIds || []).map(Number),
        juryInviteEmail: '',
        juryInviteLoading: false,
        juryInviteError: '',
        juryInviteSuccess: '',
        juryInviteNotFound: false,
        juryInviteNotFoundEmail: '',
        jurySendingInvitation: false,
        juryInviteSent: false,
        selectedGenreId: initialGenreId || '',
        diplomaBackgrounds: diplomaBgData || [],
        selectedDefaultBg: initialDiplomaBg || null,
        fullscreenUrl: null,
        diplomaPreview: null,
        contestCovers: contestCoverData || [],
        selectedCoverPath: initialCoverPath || null,
        coverPreview: null,
        get filteredBackgrounds() {
            if (!this.selectedGenreId) return [];
            return this.diplomaBackgrounds.filter(bg =>
                bg.category_ids.length === 0 || bg.category_ids.includes(parseInt(this.selectedGenreId))
            );
        },
        get filteredContestCovers() {
            if (!this.selectedGenreId) return [];
            return this.contestCovers.filter(c =>
                c.category_ids.length === 0 || c.category_ids.includes(parseInt(this.selectedGenreId))
            );
        },
        selectDefaultBg(bg) {
            if (this.selectedDefaultBg === bg.image_path) {
                this.selectedDefaultBg = null;
            } else {
                this.selectedDefaultBg = bg.image_path;
                this.diplomaPreview = null;
                const input = document.getElementById('diploma-bg-input');
                if (input) input.value = '';
            }
        },
        selectCoverFromGallery(cover) {
            if (this.selectedCoverPath === cover.image_path) {
                this.selectedCoverPath = null;
            } else {
                this.selectedCoverPath = cover.image_path;
                this.coverPreview = null;
                const input = document.getElementById('cover-image-input');
                if (input) input.value = '';
            }
        },
        onCoverUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.coverPreview = URL.createObjectURL(file);
                this.selectedCoverPath = null;
            } else {
                this.coverPreview = null;
            }
        },
        onCustomDiplomaUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.diplomaPreview = URL.createObjectURL(file);
                this.selectedDefaultBg = null;
            } else {
                this.diplomaPreview = null;
            }
        },
        get currentReps() {
            const org = this.orgs.find(o => o.id == this.selectedOrgId);
            return org ? org.reps : [];
        },
        get currentOrgCanManage() {
            const org = this.orgs.find(o => o.id == this.selectedOrgId);
            return org ? !!org.canManage : false;
        },
        addCategory() {
            this.categories.push({ name: '', description: '', age_groups: [] });
        },
        removeCategory(index) {
            this.categories.splice(index, 1);
        },
        addAgeGroup(cat) {
            cat.age_groups.push({ name: '', min_age: '', max_age: '' });
        },
        addContestAgeGroup() {
            this.contestAgeGroups.push({ name: '', min_age: '', max_age: '' });
        },
        toggleJury(id) {
            const idx = this.selectedJuries.indexOf(id);
            if (idx === -1) this.selectedJuries.push(id);
            else this.selectedJuries.splice(idx, 1);
        },
        async inviteJuryMember() {
            if (!this.juryInviteEmail || !this.selectedOrgId) return;
            this.juryInviteLoading = true;
            this.juryInviteError = '';
            this.juryInviteSuccess = '';
            this.juryInviteNotFound = false;
            this.juryInviteSent = false;
            try {
                const res = await fetch(`/organizations/${this.selectedOrgId}/add-jury-member`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: this.juryInviteEmail }),
                });
                const data = await res.json();
                if (!res.ok) {
                    if (data.not_found) {
                        this.juryInviteNotFound = true;
                        this.juryInviteNotFoundEmail = data.email;
                        return;
                    }
                    this.juryInviteError = data.error || data.message || 'Произошла ошибка.';
                    return;
                }
                const org = this.orgs.find(o => o.id == this.selectedOrgId);
                if (org && !org.reps.find(r => r.id === data.id)) {
                    org.reps.push({ id: data.id, name: data.name });
                }
                if (!this.selectedJuries.includes(data.id)) {
                    this.selectedJuries.push(data.id);
                }
                this.juryInviteSuccess = data.already_member
                    ? `${data.name} уже представитель организации и добавлен в жюри.`
                    : `${data.name} добавлен как представитель и член жюри.`;
                this.juryInviteEmail = '';
            } catch (e) {
                this.juryInviteError = 'Ошибка сети. Попробуйте ещё раз.';
            } finally {
                this.juryInviteLoading = false;
            }
        },
        async sendJuryInvitation() {
            if (!this.juryInviteNotFoundEmail || !this.selectedOrgId) return;
            this.jurySendingInvitation = true;
            this.juryInviteError = '';
            try {
                const res = await fetch(`/organizations/${this.selectedOrgId}/send-jury-invitation`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: this.juryInviteNotFoundEmail }),
                });
                const data = await res.json();
                if (!res.ok) {
                    this.juryInviteError = data.error || 'Произошла ошибка при отправке.';
                    return;
                }
                this.juryInviteSent = true;
                this.juryInviteNotFound = false;
                this.juryInviteEmail = '';
            } catch (e) {
                this.juryInviteError = 'Ошибка сети. Попробуйте ещё раз.';
            } finally {
                this.jurySendingInvitation = false;
            }
        },
    };
}
</script>
