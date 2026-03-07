<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark break-words">{{ $contest->title }}</h2>
                    <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $contest->status->color() }} shrink-0">
                        {{ $contest->status->label() }}
                    </span>
                </div>
                <p class="text-warm-gray text-sm">{{ $organization->name }}</p>
            </div>
            <a href="{{ route('evaluation.index', $organization) }}" class="self-start shrink-0 px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Progress + Finalize --}}
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-5 sm:p-6" x-data>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-dark font-medium">Оценено: {{ $evaluatedCount }} из {{ $totalCount }}</span>
                            @php $progress = $totalCount > 0 ? round(($evaluatedCount / $totalCount) * 100) : 0; @endphp
                            <span class="font-semibold {{ $evaluatedCount === $totalCount && $totalCount > 0 ? 'text-green-600' : 'text-primary' }}">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-cream-dark rounded-full h-3 overflow-hidden">
                            <div class="h-3 rounded-full transition-all duration-700 {{ $evaluatedCount === $totalCount && $totalCount > 0 ? 'bg-green-500' : 'gradient-gold' }}"
                                style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    @if($contest->isEvaluation())
                        <div class="shrink-0">
                            @if($canFinalize)
                                <button type="button"
                                    @click="$dispatch('confirm-finalize', { action: '{{ route('evaluation.finalize', [$organization, $contest]) }}' })"
                                    class="inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition text-sm">
                                    <i class="fas fa-flag-checkered mr-2"></i>Завершить оценку
                                </button>
                            @else
                                <button type="button" disabled
                                    title="Оцените все заявки перед завершением"
                                    class="inline-flex items-center px-5 py-2.5 bg-gray-100 text-gray-400 font-semibold rounded-lg text-sm cursor-not-allowed">
                                    <i class="fas fa-flag-checkered mr-2"></i>Завершить оценку
                                    @if($totalCount > $evaluatedCount)
                                        <span class="ml-2 text-xs">(осталось {{ $totalCount - $evaluatedCount }})</span>
                                    @endif
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Applications grouped by category --}}
            @if($applications->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-10 text-center">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-inbox text-2xl text-primary"></i>
                    </div>
                    <h4 class="font-serif text-lg font-semibold text-dark mb-2">Заявок нет</h4>
                    <p class="text-warm-gray text-sm">На этот конкурс не было подано ни одной заявки.</p>
                </div>
            @else
                @foreach($applications as $categoryId => $categoryApps)
                    @php
                        $category = $categoryApps->first()->category;
                        $categoryName = $category ? $category->name : 'Без номинации';
                    @endphp

                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                        <div class="px-5 py-3 bg-cream-dark border-b border-gold/10 flex items-center justify-between">
                            <h3 class="font-serif font-semibold text-dark">{{ $categoryName }}</h3>
                            <span class="text-xs text-warm-gray">{{ $categoryApps->count() }} {{ $categoryApps->count() === 1 ? 'заявка' : ($categoryApps->count() < 5 ? 'заявки' : 'заявок') }}</span>
                        </div>

                        <div class="divide-y divide-gold/10">
                            @foreach($categoryApps as $application)
                                <div x-data="{
                                    showForm: false,
                                    saving: false,
                                    success: false,
                                    error: '',
                                    status: '{{ $application->status->value }}',
                                    statusLabel: {{ Illuminate\Support\Js::from($application->status->label()) }},
                                    statusColor: {{ Illuminate\Support\Js::from($application->status->color()) }},
                                    rejectionReason: {{ Illuminate\Support\Js::from($application->rejection_reason ?? '') }},
                                    diplomaUrl: {{ Illuminate\Support\Js::from($application->diploma ? route('diplomas.download', $application->diploma) : '') }},
                                    async save(form) {
                                        this.saving = true;
                                        this.error = '';
                                        try {
                                            const resp = await fetch(form.action, {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                    'Accept': 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                },
                                                body: new FormData(form),
                                            });
                                            const json = await resp.json();
                                            if (!resp.ok) {
                                                this.error = json.errors?.rejection_reason?.[0] ?? json.message ?? 'Ошибка при сохранении.';
                                                return;
                                            }
                                            this.status = json.status;
                                            this.statusLabel = json.statusLabel;
                                            this.statusColor = json.statusColor;
                                            this.rejectionReason = json.rejectionReason ?? '';
                                            this.diplomaUrl = json.diplomaUrl ?? '';
                                            this.showForm = false;
                                            this.success = true;
                                            setTimeout(() => this.success = false, 3000);
                                        } catch {
                                            this.error = 'Ошибка сети. Попробуйте ещё раз.';
                                        } finally {
                                            this.saving = false;
                                        }
                                    }
                                }" class="px-5 py-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">

                                        {{-- Participant info --}}
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <x-user-avatar :user="$application->user" size="sm" class="shrink-0" />
                                            <div class="min-w-0">
                                                <p class="font-medium text-dark text-sm truncate">{{ $application->user->full_name }}</p>
                                                <p class="text-xs text-warm-gray truncate">{{ $application->user->email }}</p>
                                            </div>
                                        </div>

                                        {{-- Work link --}}
                                        <div class="shrink-0">
                                            @if($application->external_link)
                                                <a href="{{ $application->external_link }}" target="_blank" rel="noopener"
                                                    class="text-xs text-primary hover:underline">
                                                    <i class="fas fa-external-link-alt mr-1"></i>Ссылка
                                                </a>
                                            @elseif($application->file_path)
                                                <a href="{{ asset('storage/' . $application->file_path) }}" target="_blank"
                                                    class="text-xs text-primary hover:underline">
                                                    <i class="fas fa-file mr-1"></i>Файл
                                                </a>
                                            @endif
                                        </div>

                                        {{-- Status badge + diploma link + success flash --}}
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full"
                                                :class="statusColor"
                                                x-text="statusLabel"></span>
                                            <template x-if="diplomaUrl">
                                                <a :href="diplomaUrl" title="Скачать диплом"
                                                    class="text-gold hover:text-primary transition-colors">
                                                    <i class="fas fa-award text-sm"></i>
                                                </a>
                                            </template>
                                            <span x-show="success" x-cloak
                                                class="inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                                                <i class="fas fa-check-circle"></i> Сохранено
                                            </span>
                                        </div>

                                        {{-- Evaluate toggle --}}
                                        @if($contest->isEvaluation())
                                            <button @click="showForm = !showForm"
                                                class="shrink-0 text-xs px-3 py-1.5 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors"
                                                :class="showForm ? 'bg-primary/5' : ''">
                                                <i class="fas fa-pen mr-1"></i>
                                                <span x-text="showForm ? 'Скрыть' : 'Оценить'"></span>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Rejection reason display (reactive) --}}
                                    <div x-show="status === 'rejected' && rejectionReason" x-cloak
                                        class="mt-2 ml-10 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg">
                                        <i class="fas fa-info-circle mr-1"></i><span x-text="rejectionReason"></span>
                                    </div>

                                    {{-- Inline evaluation form (AJAX) --}}
                                    @if($contest->isEvaluation())
                                        <div x-show="showForm" x-cloak class="mt-4 pt-4 border-t border-gold/10">
                                            <form @submit.prevent="save($el)"
                                                action="{{ route('evaluation.evaluate', [$organization, $application]) }}">
                                                @csrf
                                                <div class="flex flex-col sm:flex-row gap-3">
                                                    <div class="flex-1">
                                                        <select name="status" x-model="status"
                                                            class="w-full text-sm border border-primary/20 rounded-lg px-3 py-2 focus:ring-2 focus:ring-gold/30 focus:border-gold bg-white text-dark">
                                                            <option value="participant">Участник</option>
                                                            <option value="place_1">1 место</option>
                                                            <option value="place_2">2 место</option>
                                                            <option value="place_3">3 место</option>
                                                            <option value="rejected">Отклонить</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" :disabled="saving"
                                                        class="shrink-0 px-5 py-2 gradient-gold text-dark font-semibold rounded-lg text-sm hover:opacity-90 transition disabled:opacity-60 disabled:cursor-not-allowed">
                                                        <span x-show="!saving">Сохранить</span>
                                                        <span x-show="saving" x-cloak>
                                                            <i class="fas fa-spinner fa-spin mr-1"></i>Сохранение...
                                                        </span>
                                                    </button>
                                                </div>

                                                <div x-show="status === 'rejected'" x-cloak class="mt-2">
                                                    <textarea name="rejection_reason"
                                                        placeholder="Причина отклонения (обязательно)"
                                                        rows="2"
                                                        class="w-full text-sm border border-red-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-200 focus:border-red-400 resize-none"
                                                        x-text="rejectionReason"></textarea>
                                                </div>

                                                <div x-show="error" x-cloak
                                                    class="mt-2 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg"
                                                    x-text="error"></div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>

    {{-- Finalize confirmation modal --}}
    @if($contest->isEvaluation())
        <x-confirm-modal
            name="finalize"
            title="Завершить оценку?"
            message="Конкурс перейдёт в архив, дипломы будут сформированы для всех участников. Это действие необратимо."
            icon="fa-flag-checkered"
            iconColor="text-primary"
            iconBg="bg-primary/10"
            confirmText="Завершить"
            confirmClass="gradient-gold text-dark"
        />
    @endif
</x-app-layout>
