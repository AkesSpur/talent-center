<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('contests.index') }}" class="text-warm-gray hover:text-primary transition-colors text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Конкурсы
                    </a>
                </div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">{{ $contest->title }}</h2>
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $contest->status->color() }}">
                        {{ $contest->status->label() }}
                    </span>
                    <span class="text-warm-gray text-sm">· {{ $contest->organization->name }}</span>
                    @if($contest->platformCategory)
                        <span class="text-warm-gray text-sm">· {{ $contest->platformCategory->name }}</span>
                    @endif
                </div>
            </div>

            {{-- Management buttons --}}
            <div class="flex items-center gap-2 shrink-0" x-data>
                @can('update', $contest)
                    <a href="{{ route('contests.edit', $contest) }}"
                        class="inline-flex items-center px-4 py-2 border border-primary/30 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 transition-colors">
                        <i class="fas fa-pen mr-1.5"></i>Редактировать
                    </a>
                @endcan
                @can('cancel', $contest)
                    <button type="button"
                        @click="$dispatch('confirm-cancel', { action: '{{ route('contests.cancel', $contest) }}' })"
                        class="inline-flex items-center px-4 py-2 border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors">
                        <i class="fas fa-ban mr-1.5"></i>Отменить
                    </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Cover image --}}
            @if($contest->cover_image)
                <div class="mb-6 rounded-xl overflow-hidden shadow-sm border border-gold/10 max-h-56">
                    <img src="{{ asset('storage/' . $contest->cover_image) }}"
                        class="w-full object-cover max-h-56" alt="{{ $contest->title }}">
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Main column --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Description --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        <h3 class="font-serif text-xl font-semibold text-dark mb-4">О конкурсе</h3>
                        <div class="text-dark whitespace-pre-wrap leading-relaxed">{{ $contest->description }}</div>
                    </div>

                    {{-- Rules --}}
                    @if($contest->rules)
                        <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                            <h3 class="font-serif text-xl font-semibold text-dark mb-4">Правила участия</h3>
                            <div class="text-dark whitespace-pre-wrap leading-relaxed">{{ $contest->rules }}</div>
                        </div>
                    @endif

                    {{-- Categories --}}
                    @if($contest->categories->count())
                        <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                            <h3 class="font-serif text-xl font-semibold text-dark mb-4">Номинации</h3>
                            <div class="space-y-3">
                                @foreach($contest->categories as $category)
                                    <div class="p-4 bg-cream rounded-lg border border-gold/10">
                                        <p class="font-semibold text-dark">{{ $category->name }}</p>
                                        @if($category->description)
                                            <p class="text-sm text-warm-gray mt-1">{{ $category->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">

                    {{-- Apply card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        @if($contest->isAccepting())
                            @if($hasApplied)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>Вы уже подали заявку на этот конкурс.
                                </div>
                                <a href="{{ route('dashboard.applications') }}" class="block text-center mt-3 px-4 py-2 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm">
                                    Мои заявки
                                </a>
                            @else
                                <a href="{{ route('applications.create', $contest) }}"
                                    class="block text-center px-4 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                                    <i class="fas fa-paper-plane mr-2"></i>Подать заявку
                                </a>
                            @endif
                        @elseif($contest->isPending())
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
                                <i class="fas fa-clock mr-2"></i>Приём заявок начнётся {{ $contest->applications_start_at->isoFormat('D MMMM YYYY [г.]') }}.
                            </div>
                        @elseif($contest->isEvaluation())
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-700">
                                <i class="fas fa-star mr-2"></i>Приём заявок завершён. Проводится оценка работ.
                            </div>
                        @elseif($contest->isArchive())
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-600">
                                <i class="fas fa-archive mr-2"></i>Конкурс завершён.
                            </div>
                        @elseif($contest->isCancelled())
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                                <i class="fas fa-ban mr-2"></i>Конкурс отменён.
                            </div>
                        @endif
                    </div>

                    {{-- Dates card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        <h3 class="font-semibold text-dark mb-4">Информация</h3>
                        <div class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-calendar-alt text-primary mt-0.5 w-4 text-center shrink-0"></i>
                                <div>
                                    <p class="text-warm-gray text-xs mb-1">Приём заявок</p>
                                    <p class="font-medium text-dark">
                                        с {{ $contest->applications_start_at->isoFormat('D MMMM YYYY [г.]') }}
                                    </p>
                                    <p class="font-medium text-dark">
                                        до {{ $contest->applications_end_at->isoFormat('D MMMM YYYY [г.]') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="fas fa-trophy text-primary mt-0.5 w-4 text-center shrink-0"></i>
                                <div>
                                    <p class="text-warm-gray text-xs mb-1">Результаты</p>
                                    <p class="font-medium text-dark">{{ $contest->evaluation_end_at->isoFormat('D MMMM YYYY [г.]') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Organizer card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        <h3 class="font-semibold text-dark mb-3">Организатор</h3>
                        <div class="flex items-center gap-3">
                            <x-org-avatar :organization="$contest->organization" size="sm" />
                            <div class="min-w-0">
                                <a href="{{ route('organizations.show', $contest->organization) }}"
                                    class="font-medium text-dark hover:text-primary transition-colors block truncate text-sm">
                                    {{ $contest->organization->name }}
                                </a>
                                @if($contest->organization->isVerified())
                                    <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i>Верифицирована</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Jury card --}}
                    @if($contest->juries->count())
                        <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                            <h3 class="font-semibold text-dark mb-3">Члены жюри</h3>
                            <div class="space-y-2">
                                @foreach($contest->juries as $juror)
                                    <div class="flex items-center gap-2">
                                        <x-user-avatar :user="$juror" size="xs" />
                                        <span class="text-sm text-dark">{{ $juror->full_name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
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
