<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-1.5 text-sm text-warm-gray hover:text-primary transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>Назад
                </a>
            </div>
            <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Подача заявки</h2>
            <p class="text-warm-gray mt-1">Заполните форму для участия в конкурсе</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                {{-- Contest info card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-4">
                    <div class="flex items-center gap-4">
                        <x-org-avatar :organization="$contest->organization" size="sm" />
                        <div class="min-w-0">
                            <p class="font-semibold text-dark truncate">{{ $contest->title }}</p>
                            <p class="text-sm text-warm-gray">{{ $contest->organization->name }}</p>
                            <p class="text-xs text-warm-gray mt-0.5">
                                Приём заявок до: {{ $contest->applications_end_at->format('d.m.Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Application form --}}
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                    <form method="POST" action="{{ route('applications.store', $contest) }}"
                          enctype="multipart/form-data"
                          x-data="{
                            uploadMode: '{{ old('external_link') ? 'link' : 'file' }}',
                            selectedFile: null,
                            showTeacher: {{ old('teacher_name') ? 'true' : 'false' }},
                            selectedCategoryId: '{{ old('category_id', '') }}',
                            allAgeGroups: {{ \Illuminate\Support\Js::from($ageGroupsData) }},
                            get filteredAgeGroups() {
                                if (this.selectedCategoryId) {
                                    return this.allAgeGroups.filter(ag => ag.contest_category_id == this.selectedCategoryId);
                                }
                                return this.allAgeGroups.filter(ag => !ag.contest_category_id);
                            },
                            formatSize(bytes) { if (!bytes) return ''; const mb = bytes / 1024 / 1024; return mb >= 1 ? mb.toFixed(1) + ' МБ' : (bytes / 1024).toFixed(0) + ' КБ'; }
                          }"
                          class="space-y-6">
                        @csrf
                        <input type="hidden" name="contest_id" value="{{ $contest->id }}">

                        {{-- "Submit as" dropdown (only if user has children) --}}
                        @if($children->count())
                            <div>
                                <label for="submitted_for_user_id" class="block text-sm font-medium text-dark mb-2">
                                    Подать заявку от имени <span class="text-red-500">*</span>
                                </label>
                                <select id="submitted_for_user_id" name="submitted_for_user_id"
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm">
                                    @unless(in_array(auth()->id(), $alreadyAppliedIds))
                                        <option value="{{ auth()->id() }}" {{ old('submitted_for_user_id') == auth()->id() || ! old('submitted_for_user_id') ? 'selected' : '' }}>
                                            {{ auth()->user()->full_name }} (я)
                                        </option>
                                    @endunless
                                    @foreach($children as $child)
                                        @unless(in_array($child->id, $alreadyAppliedIds))
                                            <option value="{{ $child->id }}" {{ old('submitted_for_user_id') == $child->id ? 'selected' : '' }}>
                                                {{ $child->full_name }}
                                            </option>
                                        @endunless
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('submitted_for_user_id')" />
                            </div>
                        @endif

                        {{-- Category selection --}}
                        @if($contest->categories->count())
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-dark mb-2">Номинация</label>
                                <select id="category_id" name="category_id" x-model="selectedCategoryId"
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm">
                                    <option value="">— Без номинации —</option>
                                    @foreach($contest->categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                            </div>
                        @endif

                        {{-- Age group selection --}}
                        <div x-show="filteredAgeGroups.length > 0" x-cloak>
                            <label for="age_group_id" class="block text-sm font-medium text-dark mb-2">Возрастная группа</label>
                            <select id="age_group_id" name="age_group_id"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm">
                                <option value="">— Без возрастной группы —</option>
                                <template x-for="ag in filteredAgeGroups" :key="ag.id">
                                    <option :value="ag.id" :selected="ag.id == '{{ old('age_group_id', '') }}'"
                                        x-text="ag.name + (ag.min_age || ag.max_age ? ' (' + (ag.min_age || '') + '–' + (ag.max_age || '') + ' лет)' : '')"></option>
                                </template>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('age_group_id')" />
                        </div>

                        {{-- Teacher in diploma --}}
                        <div>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="showTeacher"
                                    class="rounded border-primary/30 text-primary focus:ring-primary/30">
                                <span class="text-sm font-medium text-dark">Добавить преподавателя в диплом</span>
                            </label>

                            <div x-show="showTeacher" x-cloak class="mt-3">
                                <input type="text" name="teacher_name"
                                    value="{{ old('teacher_name') }}"
                                    placeholder="ФИО преподавателя"
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm" />
                                <x-input-error class="mt-2" :messages="$errors->get('teacher_name')" />
                            </div>
                        </div>

                        {{-- Upload mode toggle --}}
                        <div>
                            <label class="block text-sm font-medium text-dark mb-3">
                                Работа участника <span class="text-red-500">*</span>
                            </label>

                            <div class="flex gap-2 mb-4">
                                <button type="button"
                                    @click="uploadMode = 'file'"
                                    :class="uploadMode === 'file' ? 'bg-primary text-white border-primary' : 'border-primary/30 text-primary hover:bg-primary/5'"
                                    class="px-4 py-2 border rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-upload mr-1.5"></i>Загрузить файл
                                </button>
                                <button type="button"
                                    @click="uploadMode = 'link'"
                                    :class="uploadMode === 'link' ? 'bg-primary text-white border-primary' : 'border-primary/30 text-primary hover:bg-primary/5'"
                                    class="px-4 py-2 border rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-link mr-1.5"></i>Ссылка на файл
                                </button>
                            </div>

                            {{-- File upload --}}
                            <div x-show="uploadMode === 'file'">
                                <div :class="selectedFile ? 'border-green-400 bg-green-50' : 'border-primary/20 hover:border-primary/40'"
                                     class="border-2 border-dashed rounded-lg p-6 text-center transition-colors">
                                    <input type="file" name="file" id="application-file"
                                        accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx"
                                        class="hidden"
                                        @change="selectedFile = $event.target.files[0]">
                                    <label for="application-file" class="cursor-pointer block">
                                        {{-- No file selected --}}
                                        <template x-if="!selectedFile">
                                            <div>
                                                <i class="fas fa-cloud-upload-alt text-3xl text-primary/40 mb-3 block"></i>
                                                <p class="text-sm font-medium text-dark mb-1">Нажмите для выбора файла</p>
                                                <p class="text-xs text-warm-gray">JPG, PNG, GIF, PDF, DOC, DOCX — до 4 МБ</p>
                                            </div>
                                        </template>
                                        {{-- File selected --}}
                                        <template x-if="selectedFile">
                                            <div>
                                                <i class="fas fa-check-circle text-3xl text-green-500 mb-3 block"></i>
                                                <p class="text-sm font-semibold text-green-700 mb-1 truncate" x-text="selectedFile.name"></p>
                                                <p class="text-xs text-green-600" x-text="formatSize(selectedFile.size)"></p>
                                                <p class="text-xs text-warm-gray mt-2">Нажмите, чтобы изменить файл</p>
                                            </div>
                                        </template>
                                    </label>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('file')" />
                            </div>

                            {{-- Link input --}}
                            <div x-show="uploadMode === 'link'" x-cloak>
                                <input type="url" name="external_link"
                                    value="{{ old('external_link') }}"
                                    placeholder="https://drive.google.com/file/..."
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm" />
                                <p class="text-xs text-warm-gray mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Укажите ссылку на Google Drive, Яндекс.Диск или другое облачное хранилище. Убедитесь, что файл открыт по ссылке.
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('external_link')" />
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="flex gap-4 pt-2">
                            <button type="submit"
                                class="px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                                <i class="fas fa-paper-plane mr-2"></i>Подать заявку
                            </button>
                            <a href="{{ route('contests.show', $contest) }}"
                                class="px-6 py-3 border border-primary/30 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors">
                                Отмена
                            </a>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
