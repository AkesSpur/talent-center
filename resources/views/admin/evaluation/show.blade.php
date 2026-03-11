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
                <p class="text-warm-gray text-sm">{{ $contest->organization->name }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                {{-- Regenerate diplomas --}}
                <span x-data>
                    <button type="button"
                        @click="$dispatch('confirm-regenerate-diplomas', { action: '{{ route('admin.contests.regenerate-diplomas', $contest) }}' })"
                        class="inline-flex items-center px-4 py-2 border border-primary/20 text-primary font-medium rounded-lg hover:bg-primary/5 transition-colors text-sm">
                        <i class="fas fa-redo mr-2"></i>Пересчитать дипломы
                    </button>
                </span>
                <a href="{{ route('admin.contests.applications', $contest) }}"
                    class="px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Назад
                </a>
            </div>
        </div>
    </x-slot>

    <x-confirm-modal
        name="regenerate-diplomas"
        title="Пересчитать дипломы"
        message="Все дипломы для оценённых заявок этого конкурса будут сгенерированы заново. Продолжить?"
        icon="fa-redo"
        iconColor="text-primary"
        iconBg="bg-primary/10"
        confirmText="Пересчитать"
        confirmClass="gradient-gold text-dark"
    />

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Stats bar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
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
                    <div class="shrink-0 text-xs text-warm-gray bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                        <i class="fas fa-shield-alt text-yellow-600 mr-1"></i>
                        Режим администратора — переопределение оценок
                    </div>
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
                                <div x-data="{ showForm: false, status: '{{ $application->status->value }}' }" class="px-5 py-4">
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

                                        {{-- Status badge + diploma --}}
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $application->status->color() }}">
                                                {{ $application->status->label() }}
                                            </span>
                                            @if($application->diploma)
                                                <a href="{{ route('diplomas.download', $application->diploma) }}"
                                                    title="Скачать диплом"
                                                    class="text-gold hover:text-primary transition-colors">
                                                    <i class="fas fa-award text-sm"></i>
                                                </a>
                                            @endif
                                        </div>

                                        {{-- Override toggle --}}
                                        <button @click="showForm = !showForm"
                                            class="shrink-0 text-xs px-3 py-1.5 border border-yellow-300 text-yellow-700 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                                            <i class="fas fa-edit mr-1"></i>
                                            <span x-text="showForm ? 'Скрыть' : 'Изменить'"></span>
                                        </button>
                                    </div>

                                    {{-- Rejection reason display --}}
                                    @if($application->status === \App\Enums\ApplicationStatus::Rejected && $application->rejection_reason)
                                        <div class="mt-2 ml-10 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg">
                                            <i class="fas fa-info-circle mr-1"></i>{{ $application->rejection_reason }}
                                        </div>
                                    @endif

                                    {{-- Inline override form --}}
                                    <div x-show="showForm" x-cloak class="mt-4 pt-4 border-t border-gold/10">
                                        <form method="POST"
                                            action="{{ route('admin.evaluation.evaluate', $application) }}">
                                            @csrf
                                            <div class="flex flex-col sm:flex-row gap-3">
                                                <div class="flex-1">
                                                    <select name="status" x-model="status"
                                                        class="w-full text-sm border border-yellow-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-200 focus:border-yellow-400 bg-white text-dark">
                                                        <option value="participant">Участник</option>
                                                        <option value="place_1">1 место</option>
                                                        <option value="place_2">2 место</option>
                                                        <option value="place_3">3 место</option>
                                                        <option value="rejected">Отклонить</option>
                                                    </select>
                                                </div>
                                                <button type="submit"
                                                    class="shrink-0 px-5 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg text-sm transition">
                                                    Сохранить
                                                </button>
                                            </div>

                                            <div x-show="status === 'rejected'" x-cloak class="mt-2">
                                                <textarea name="rejection_reason"
                                                    placeholder="Причина отклонения (обязательно)"
                                                    rows="2"
                                                    class="w-full text-sm border border-red-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-200 focus:border-red-400 resize-none">{{ $application->rejection_reason }}</textarea>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</x-app-layout>
