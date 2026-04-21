<x-app-layout>
    <x-slot name="header">
        <div>
            <x-breadcrumbs :items="[
                ['label' => 'Конкурсы', 'url' => route('contests.index')],
                ['label' => $contest->title, 'url' => route('contests.show', $contest)],
                ['label' => 'Подать заявку', 'url' => '#'],
            ]" />
            <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Подача заявки</h2>
            <p class="text-warm-gray mt-1">Заполните форму для участия в конкурсе</p>
        </div>
    </x-slot>

    @php
        $categoriesPreviewData = [];
        foreach ($contest->categories as $cat) {
            $categoriesPreviewData[(string) $cat->id] = $cat->name;
        }
    @endphp

    <style>
        [x-cloak] { display: none !important; }
        .diploma-corner {
            position: absolute;
            width: 18px;
            height: 18px;
        }
        .diploma-corner-tl { top: 12px; left: 12px; border-top: 2px solid rgba(212,175,55,0.55); border-left: 2px solid rgba(212,175,55,0.55); }
        .diploma-corner-tr { top: 12px; right: 12px; border-top: 2px solid rgba(212,175,55,0.55); border-right: 2px solid rgba(212,175,55,0.55); }
        .diploma-corner-bl { bottom: 12px; left: 12px; border-bottom: 2px solid rgba(212,175,55,0.55); border-left: 2px solid rgba(212,175,55,0.55); }
        .diploma-corner-br { bottom: 12px; right: 12px; border-bottom: 2px solid rgba(212,175,55,0.55); border-right: 2px solid rgba(212,175,55,0.55); }
    </style>

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
                                @if($contest->applications_end_at)
                                    Приём заявок до: {{ $contest->applications_end_at->format('d.m.Y') }}
                                @else
                                    Бессрочный конкурс
                                @endif
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
                            externalLink: '{{ old('external_link', '') }}',
                            showTeacher: {{ old('teacher_name') ? 'true' : 'false' }},
                            teacherName: '{{ old('teacher_name', '') }}',
                            selectedCategoryId: '{{ old('category_id', '') }}',
                            selectedAgeGroupId: '{{ old('age_group_id', '') }}',
                            submittedForUserId: '{{ old('submitted_for_user_id', auth()->id()) }}',
                            confirmChecked: false,
                            allAgeGroups: {{ \Illuminate\Support\Js::from($ageGroupsData) }},
                            categoriesData: {{ \Illuminate\Support\Js::from($categoriesPreviewData) }},
                            usersData: {{ \Illuminate\Support\Js::from($usersPreviewData) }},
                            diplomaBg: @js($diplomaBgUrl),
                            juryMembers: @js($juryMembers),
                            orgName: @js($contest->organization->name),
                            orgCity: @js($contest->organization->city),
                            contestTitle: @js($contest->title),
                            contactEmail: @js($contactEmail),
                            previewDate: @js($previewDate),
                            get filteredAgeGroups() {
                                if (this.selectedCategoryId) {
                                    return this.allAgeGroups.filter(ag => ag.contest_category_id == this.selectedCategoryId);
                                }
                                return this.allAgeGroups.filter(ag => !ag.contest_category_id);
                            },
                            get hasWork() {
                                return this.uploadMode === 'file'
                                    ? this.selectedFile !== null
                                    : this.externalLink.trim().length > 0;
                            },
                            get canSubmit() {
                                return this.confirmChecked;
                            },
                            get participantName() {
                                return (this.usersData[String(this.submittedForUserId)] || {}).fullName || '';
                            },
                            get previewLastName() {
                                return (this.usersData[String(this.submittedForUserId)] || {}).lastName || '';
                            },
                            get previewFirstPat() {
                                return (this.usersData[String(this.submittedForUserId)] || {}).firstPat || '';
                            },
                            get previewInstitution() {
                                return (this.usersData[String(this.submittedForUserId)] || {}).institution || '';
                            },
                            get previewGroup() {
                                return (this.usersData[String(this.submittedForUserId)] || {}).group || '';
                            },
                            get previewCity() {
                                return (this.usersData[String(this.submittedForUserId)] || {}).city || '';
                            },
                            get categoryName() {
                                if (!this.selectedCategoryId) return '';
                                return this.categoriesData[String(this.selectedCategoryId)] || '';
                            },
                            get ageGroupLabel() {
                                if (!this.selectedAgeGroupId) return '';
                                const ag = this.allAgeGroups.find(a => String(a.id) === String(this.selectedAgeGroupId));
                                if (!ag) return '';
                                if (ag.min_age || ag.max_age) return ag.name + ' (' + (ag.min_age || '') + '–' + (ag.max_age || '') + ' лет)';
                                return ag.name;
                            },
                            get workLabel() {
                                if (this.uploadMode === 'file') return this.selectedFile ? this.selectedFile.name : '';
                                return this.externalLink;
                            },
                            formatSize(bytes) {
                                if (!bytes) return '';
                                const mb = bytes / 1024 / 1024;
                                return mb >= 1 ? mb.toFixed(1) + ' МБ' : (bytes / 1024).toFixed(0) + ' КБ';
                            }
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
                                    x-model="submittedForUserId"
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm">
                                    @unless(in_array(auth()->id(), $alreadyAppliedIds))
                                        <option value="{{ auth()->id() }}">{{ auth()->user()->full_name }} (я)</option>
                                    @endunless
                                    @foreach($children as $child)
                                        @unless(in_array($child->id, $alreadyAppliedIds))
                                            <option value="{{ $child->id }}">{{ $child->full_name }}</option>
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
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                            </div>
                        @endif

                        {{-- Age group selection --}}
                        <div x-show="filteredAgeGroups.length > 0" x-cloak>
                            <label for="age_group_id" class="block text-sm font-medium text-dark mb-2">Возрастная группа</label>
                            <select id="age_group_id" name="age_group_id"
                                x-model="selectedAgeGroupId"
                                class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm">
                                <option value="">— Без возрастной группы —</option>
                                <template x-for="ag in filteredAgeGroups" :key="ag.id">
                                    <option :value="ag.id"
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
                                    x-model="teacherName"
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
                                        <template x-if="!selectedFile">
                                            <div>
                                                <i class="fas fa-cloud-upload-alt text-3xl text-primary/40 mb-3 block"></i>
                                                <p class="text-sm font-medium text-dark mb-1">Нажмите для выбора файла</p>
                                                <p class="text-xs text-warm-gray">JPG, PNG, GIF, PDF, DOC, DOCX — до 4 МБ</p>
                                            </div>
                                        </template>
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
                                    x-model="externalLink"
                                    placeholder="https://drive.google.com/file/..."
                                    class="w-full px-4 py-3 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm" />
                                <p class="text-xs text-warm-gray mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Укажите ссылку на Google Drive, Яндекс.Диск или другое облачное хранилище. Убедитесь, что файл открыт по ссылке.
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('external_link')" />
                            </div>
                        </div>

                        {{-- ════════════════════════
                             DIPLOMA PREVIEW (real template, sample stamp)
                             ════════════════════════ --}}
                        <div class="border-t border-gold/20 pt-6 space-y-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 gradient-gold rounded-lg flex items-center justify-center shrink-0">
                                    <i class="fas fa-medal text-white text-xs"></i>
                                </div>
                                <h3 class="font-serif font-semibold text-dark">Образец диплома</h3>
                            </div>

                        {{-- Frontend diploma preview — fully reactive, no backend calls --}}
                            <div
                                class="relative w-full overflow-hidden rounded-lg"
                                style="aspect-ratio: 794 / 1123; border: 1.5px solid rgba(212,175,55,0.35);"
                                x-data="{ scale: 1 }"
                                x-init="
                                    const upd = () => { scale = $el.offsetWidth / 794 };
                                    $nextTick(upd);
                                    window.addEventListener('resize', upd);
                                "
                            >
                                <div :style="`position:absolute;top:0;left:0;width:794px;height:1123px;transform:scale(${scale});transform-origin:top left;background-color:#FAF8F5;overflow:hidden;font-family:Georgia,serif;`">

                                    {{-- Background image --}}
                                    <template x-if="diplomaBg">
                                        <div :style="`position:absolute;inset:0;background-image:url('${diplomaBg}');background-size:cover;background-position:center;background-repeat:no-repeat;`"></div>
                                    </template>

                                    {{-- "Образец" watermark --}}
                                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);font-size:62pt;font-weight:bold;font-style:italic;color:rgba(190,0,0,0.5);border:7px solid rgba(190,0,0,0.5);padding:15px 45px;border-radius:6px;white-space:nowrap;z-index:100;text-transform:uppercase;letter-spacing:0.08em;pointer-events:none;">Образец</div>

                                    {{-- Main content --}}
                                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;padding:53px 53px 38px 53px;">

                                        {{-- Org block --}}
                                        <div style="text-align:right;margin-bottom:30px;max-width:65%;margin-left:auto;">
                                            <div style="font-size:10pt;font-weight:bold;color:#000;text-transform:uppercase;" x-text="orgName"></div>
                                            <template x-if="orgCity">
                                                <div style="font-size:10pt;font-weight:bold;color:#000;line-height:1;margin-top:4px;" x-text="'г.'+orgCity"></div>
                                            </template>
                                            <div style="font-size:16pt;font-weight:bold;color:#763609;" x-text="contestTitle"></div>
                                        </div>

                                        {{-- "Диплом" heading --}}
                                        <div style="text-align:right;font-size:56pt;margin-top:60px;color:#ca9848;font-weight:bold;font-style:italic;letter-spacing:0.01em;margin-bottom:-8px;">Диплом</div>
                                        <div style="text-align:right;font-size:22pt;font-weight:bold;color:#aa8345;margin-bottom:8px;">участника</div>
                                        <div style="width:227px;height:2px;background:linear-gradient(90deg,transparent,#A67C00,transparent);margin:8px 0 8px auto;"></div>
                                        <div style="text-align:right;font-size:12pt;font-weight:bold;color:#aa8345;margin-bottom:4px;">награждается</div>

                                        {{-- Participant name (reactive) --}}
                                        <div style="text-align:right;font-size:21pt;font-weight:bold;font-style:italic;color:#000;line-height:1.2;" x-text="previewLastName"></div>
                                        <div style="text-align:right;font-size:21pt;font-weight:bold;font-style:italic;color:#000;line-height:1.2;margin-bottom:19px;" x-text="previewFirstPat"></div>

                                        {{-- Age group (reactive) --}}
                                        <template x-if="ageGroupLabel">
                                            <div style="text-align:right;font-size:12pt;color:#000;line-height:1.7;"><span style="color:#aa8345;">возрастная группа: </span><span x-text="ageGroupLabel"></span></div>
                                        </template>

                                        {{-- Teacher (reactive) --}}
                                        <template x-if="showTeacher && teacherName">
                                            <div style="text-align:right;font-size:12pt;color:#000;line-height:1.7;"><span style="color:#aa8345;">Преподаватель: </span><span x-text="teacherName"></span></div>
                                        </template>

                                        {{-- City / Institution / group --}}
                                        <template x-if="previewCity">
                                            <div style="text-align:right;font-size:12pt;color:#000;line-height:1.7;margin-top:8px;"><span style="color:#aa8345;">город: </span><span x-text="previewCity"></span></div>
                                        </template>
                                        <template x-if="previewInstitution">
                                            <div style="text-align:right;font-size:12pt;color:#000;line-height:1.7;margin-top:8px;"><span style="color:#aa8345;">учреждение: </span><span x-text="previewInstitution"></span></div>
                                        </template>
                                        <template x-if="previewGroup">
                                            <div style="text-align:right;font-size:12pt;color:#000;line-height:1.7;"><span style="color:#aa8345;">класс/группа: </span><span x-text="previewGroup"></span></div>
                                        </template>

                                        {{-- Category --}}
                                        <template x-if="categoryName">
                                            <div style="text-align:right;font-size:12pt;color:#000;line-height:1.7;"><span style="color:#aa8345;">номинация: </span>«<span x-text="categoryName"></span>»</div>
                                        </template>
                                    </div>

                                    {{-- Bottom-left: jury + meta --}}
                                    <div style="position:absolute;bottom:38px;left:53px;width:321px;">
                                        <template x-if="juryMembers.length > 0">
                                            <div>
                                                <div style="font-size:11pt;font-weight:bold;color:#000;">Жюри:</div>
                                                <template x-for="m in juryMembers" :key="m">
                                                    <div style="font-size:11pt;font-weight:bold;color:#000;" x-text="m"></div>
                                                </template>
                                            </div>
                                        </template>
                                        <div style="font-size:10pt;color:#000;margin-top:15px;">Диплом № —<br><span x-text="previewDate"></span></div>
                                        <div style="font-size:9pt;color:#000;margin-top:15px;">talant-centr.ru<br><span x-text="contactEmail"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Confirmation checkbox (always visible) --}}
                        <label
                            class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-all duration-200"
                            :class="confirmChecked
                                ? 'border-gold/40 bg-gold/5'
                                : 'border-primary/10 bg-cream/40 hover:border-gold/25 hover:bg-gold/[0.03]'"
                        >
                            <input type="checkbox" x-model="confirmChecked"
                                class="w-4 h-4 mt-0.5 shrink-0 rounded border-primary/30 text-primary focus:ring-primary/20 cursor-pointer">
                            <div>
                                <p class="text-sm font-semibold text-dark leading-snug">Заполнено верно, мною проверено</p>
                                <p class="text-xs text-warm-gray mt-0.5 leading-relaxed">Я подтверждаю, что все данные заполнены верно и соответствуют действительности, все необходимые согласия имеются, с
                                    @if($offerDocUrl)
                                        <a href="{{ $offerDocUrl }}" target="_blank" rel="noopener noreferrer" class="underline hover:opacity-80">договором-оферты</a>
                                    @else
                                        договором-оферты
                                    @endif
                                    ознакомлен, условия принимаю.</p>
                            </div>
                        </label>

                        {{-- Org fee block (paid contests only) --}}
                        @if($contest->is_paid)
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                                        <i class="fas fa-ruble-sign text-amber-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-dark">Организационный взнос</p>
                                        <p class="text-sm text-dark">Сумма оргвзноса: <span class="font-bold">{{ number_format($contest->entry_fee, 0, '.', ' ') }} рублей</span></p>
                                        <p class="text-xs text-warm-gray mt-0.5">После нажатия кнопки вы будете перенаправлены на страницу оплаты.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Submit --}}
                        <div class="flex gap-3 pt-2">
                            <button type="submit"
                                :disabled="!canSubmit"
                                :class="canSubmit
                                    ? 'gradient-gold text-dark hover:opacity-90 shadow-sm cursor-pointer'
                                    : 'bg-warm-gray/10 text-warm-gray/50 cursor-not-allowed border border-warm-gray/15'"
                                class="flex-1 py-3 font-semibold rounded-lg transition-all duration-300 text-sm">
                                <i class="fas fa-paper-plane mr-2"
                                   :class="canSubmit ? '' : 'opacity-40'"></i>@if($contest->is_paid)Подать заявку и оплатить оргвзнос {{ number_format($contest->entry_fee, 0, '.', ' ') }} рублей@else Подать заявку@endif
                            </button>
                            <a href="{{ route('contests.show', $contest) }}"
                                class="px-6 py-3 border border-primary/30 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors text-sm whitespace-nowrap">
                                Отмена
                            </a>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
