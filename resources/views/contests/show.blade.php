@extends('layouts.public')

@section('title', $contest->title . ' — Талант-центр')
@section('description', Str::limit(strip_tags($contest->description ?? ''), 160))

@section('content')

    {{-- Page header --}}
    <div class="pattern-bg py-6 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4" x-data>
                <div class="min-w-0 flex-1">
                    <x-breadcrumbs :items="[
                        ['label' => 'Конкурсы', 'url' => route('contests.index')],
                        ['label' => $contest->title, 'url' => '#'],
                    ]" />
                    <h1 class="font-serif text-xl sm:text-2xl font-bold text-dark">{{ $contest->title }}</h1>
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
                <div class="flex items-center gap-2 shrink-0">
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
        </div>
    </div>

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
                        <div class="text-dark leading-relaxed rich-content">{!! clean($contest->description) !!}</div>
                    </div>

                    {{-- Rules --}}
                    @if($contest->rules)
                        <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                            <h3 class="font-serif text-xl font-semibold text-dark mb-4">Правила участия</h3>
                            <div class="text-dark leading-relaxed rich-content">{!! clean($contest->rules) !!}</div>
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

                    {{-- Copy link card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-4" x-data="{ copied: false }">
                        <button
                            type="button"
                            @click="navigator.clipboard.writeText('{{ route('contests.show', $contest) }}').then(() => { copied = true; setTimeout(() => copied = false, 3000) })"
                            class="w-full px-4 py-2.5 border border-green-300 text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors text-sm font-medium flex items-center justify-center gap-2">
                            <i class="fas fa-link"></i>
                            Скопировать ссылку на конкурс
                        </button>
                        <p x-show="copied" x-transition class="text-xs text-green-600 text-center mt-2">
                            <i class="fas fa-check mr-1"></i>Ссылка скопирована в буфер обмена
                        </p>
                    </div>

                    {{-- Apply card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6" x-data="{ showAuthModal: false }">
                        @if($contest->isAccepting())
                            @if($hasApplied)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>Вы уже подали заявку на этот конкурс.
                                </div>
                                <a href="{{ route('dashboard.applications') }}" class="block text-center mt-3 px-4 py-2 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm">
                                    Мои заявки
                                </a>
                            @elseif($contest->isApplicationLimitReached())
                                <div class="relative group">
                                    <button type="button" disabled
                                        class="block w-full text-center px-4 py-3 bg-warm-gray/15 text-warm-gray/50 font-semibold rounded-lg cursor-not-allowed select-none">
                                        <i class="fas fa-paper-plane mr-2 opacity-50"></i>Подать заявку
                                    </button>
                                    <span class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-dark text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-150 z-10">
                                        Лимит заявок исчерпан
                                        <span class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-dark"></span>
                                    </span>
                                </div>
                            @else
                                @auth
                                    <a href="{{ route('applications.create', $contest) }}"
                                        class="block text-center px-4 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                                        <i class="fas fa-paper-plane mr-2"></i>Подать заявку
                                    </a>
                                @endauth
                                @guest
                                    <button type="button" @click="showAuthModal = true"
                                        class="block w-full text-center px-4 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                                        <i class="fas fa-paper-plane mr-2"></i>Подать заявку
                                    </button>
                                @endguest
                            @endif
                            @if($contest->isPermanent() && auth()->check() && (auth()->user()->isAdmin() || auth()->user()->canInOrg('evaluate', $contest->organization)))
                                <a href="{{ route('evaluation.show', [$contest->organization, $contest]) }}"
                                    class="block text-center mt-3 px-4 py-2.5 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm">
                                    <i class="fas fa-gavel mr-2"></i>Оценить заявки
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
                            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->canInOrg('evaluate', $contest->organization)))
                                <a href="{{ route('evaluation.show', [$contest->organization, $contest]) }}"
                                    class="block text-center mt-3 px-4 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                                    <i class="fas fa-star mr-2"></i>Оценить заявки
                                </a>
                            @endif
                        @elseif($contest->isArchive())
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-600">
                                <i class="fas fa-archive mr-2"></i>Конкурс завершён.
                            </div>
                        @elseif($contest->isCancelled())
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                                <i class="fas fa-ban mr-2"></i>Конкурс отменён.
                            </div>
                        @endif

                        {{-- Guest auth modal --}}
                        <div x-show="showAuthModal" x-cloak
                            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50"
                            x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0">
                            <div @click.outside="showAuthModal = false"
                                class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 space-y-4">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-user-lock text-primary text-xl"></i>
                                    </div>
                                    <h3 class="font-serif text-lg font-semibold text-dark">Требуется вход</h3>
                                    <p class="text-sm text-warm-gray mt-2">Для участия в конкурсе необходимо войти либо зарегистрироваться.</p>
                                </div>
                                <div class="flex flex-col gap-2 pt-2">
                                    <a href="{{ route('login') }}"
                                        class="block text-center px-4 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                                        <i class="fas fa-sign-in-alt mr-2"></i>Войти
                                    </a>
                                    <a href="{{ route('register') }}"
                                        class="block text-center px-4 py-3 border border-primary text-primary font-semibold rounded-lg hover:bg-primary/5 transition-colors">
                                        <i class="fas fa-user-plus mr-2"></i>Зарегистрироваться
                                    </a>
                                    <button @click="showAuthModal = false"
                                        class="block w-full text-center px-4 py-2 text-warm-gray hover:text-dark transition-colors text-sm">
                                        Вернуться к конкурсу
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dates card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        <h3 class="font-semibold text-dark mb-4">Информация</h3>
                        <div class="space-y-4 text-sm">
                            @if($contest->regulations_url)
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-file-alt text-primary mt-0.5 w-4 text-center shrink-0"></i>
                                    <div>
                                        <a href="{{ $contest->regulations_url }}" target="_blank" rel="noopener noreferrer"
                                            class="font-medium text-primary hover:underline">Положение о конкурсе</a>
                                    </div>
                                </div>
                            @endif
                            <div class="flex items-start gap-3">
                                <i class="fas fa-calendar-alt text-primary mt-0.5 w-4 text-center shrink-0"></i>
                                <div>
                                    <p class="text-warm-gray text-xs mb-1">Приём заявок</p>
                                    <p class="font-medium text-dark">
                                        с {{ $contest->applications_start_at->isoFormat('D MMMM YYYY [г.]') }}
                                    </p>
                                    @if($contest->applications_end_at)
                                        <p class="font-medium text-dark">
                                            до {{ $contest->applications_end_at->isoFormat('D MMMM YYYY [г.]') }}
                                        </p>
                                    @else
                                        <p class="font-medium text-dark">бессрочно</p>
                                    @endif
                                </div>
                            </div>
                            @if($contest->evaluation_end_at)
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-trophy text-primary mt-0.5 w-4 text-center shrink-0"></i>
                                    <div>
                                        <p class="text-warm-gray text-xs mb-1">Результаты</p>
                                        <p class="font-medium text-dark">{{ $contest->evaluation_end_at->isoFormat('D MMMM YYYY [г.]') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Statistics card --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-6">
                        <h3 class="font-semibold text-dark mb-3">Статистика</h3>
                        <div class="flex items-center gap-3 text-sm">
                            <i class="fas fa-users text-primary w-4 text-center shrink-0"></i>
                            <div>
                                <span class="text-warm-gray">Подано заявок:</span>
                                <span class="font-semibold text-dark ml-1">{{ $applicationsCount }}</span>
                                @if($contest->hasApplicationLimit())
                                    <span class="text-warm-gray"> из </span>
                                    <span class="font-semibold text-dark">{{ $contest->application_limit }}</span>
                                @endif
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
                                    class="font-medium text-dark hover:text-primary transition-colors block break-words text-sm">
                                    {{ $contest->organization->name }}
                                </a>
                                @if($contest->organization->isVerified())
                                    <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i>Верифицирована</span>
                                @endif
                                @if($contest->organization->createdBy)
                                    <p class="text-xs text-warm-gray mt-1">
                                        <i class="fas fa-user mr-1"></i>{{ $contest->organization->createdBy->full_name }}
                                    </p>
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

@endsection
