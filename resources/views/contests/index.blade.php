@extends('layouts.public')

@section('title', 'Конкурсы — Талант-центр')

@section('head')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection

@section('content')

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-8 sm:py-10 px-4 text-center">
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-dark mb-4">
            Все конкурсы
        </h2>
        <p class="text-warm-gray text-md max-w-2xl mx-auto">
            Найдите конкурс по своим интересам и участвуйте в творческих состязаниях
        </p>
    </section>

    <!-- ========== MAIN CONTENT ========== -->
    <main
        x-data="{
            search: '',
            category: '',
            modal: null,
            check(title, catId) {
                const ms = this.search === '' || title.toLowerCase().includes(this.search.toLowerCase());
                const mc = this.category === '' || String(catId) === this.category;
                return ms && mc;
            }
        }"
        @keydown.escape.window="modal = null"
        class="py-10"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Filter bar --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gold/10 p-4 flex flex-wrap gap-3">
                {{-- Search --}}
                <div class="flex-1 min-w-[200px] relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-warm-gray text-sm pointer-events-none"></i>
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Поиск по названию..."
                        class="w-full pl-10 pr-4 py-2.5 border border-primary/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm bg-cream/50"
                    />
                </div>

                {{-- Category --}}
                @if($platformCategories->count())
                    <select
                        x-model="category"
                        class="px-3 py-2.5 border border-primary/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm bg-cream/50 text-dark"
                    >
                        <option value="">Все жанры</option>
                        @foreach($platformCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Contest grid --}}
            @if($contests->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($contests as $contest)
                        @php
                            $contestData = [
                                'title'              => $contest->title,
                                'description'        => $contest->description,
                                'cover_image'        => $contest->cover_image ? asset('storage/' . $contest->cover_image) : null,
                                'category'           => $contest->platformCategory?->name,
                                'org_name'           => $contest->organization->name,
                                'applications_start' => $contest->applications_start_at->isoFormat('D MMM'),
                                'applications_end'   => $contest->applications_end_at->isoFormat('D MMM'),
                                'evaluation_end'     => $contest->evaluation_end_at->isoFormat('D MMM'),
                                'apply_url'          => route('applications.create', $contest),
                                'show_url'           => route('contests.show', $contest),
                                'categories'         => $contest->categories->map(fn ($c) => $c->name)->values()->toArray(),
                                'regulations_url'    => $contest->regulations_url,
                            ];
                        @endphp
                        <div
                            x-show="check({{ Js::from($contest->title) }}, '{{ $contest->platform_category_id ?? '' }}')"
                            @click="modal = {{ Js::from($contestData) }}"
                            class="bg-white rounded-2xl shadow-sm border border-gold/10 hover-lift flex flex-col overflow-hidden cursor-pointer"
                        >
                            {{-- Cover area --}}
                            <div class="relative h-44 overflow-hidden shrink-0">
                                @if($contest->cover_image)
                                    <img src="{{ asset('storage/' . $contest->cover_image) }}"
                                        class="w-full h-full object-cover" alt="{{ $contest->title }}">
                                @else
                                    <div class="w-full h-full gradient-gold flex items-center justify-center">
                                        <i class="fas fa-award text-white/25 text-7xl"></i>
                                    </div>
                                @endif

                                {{-- Category badge overlaid --}}
                                @if($contest->platformCategory)
                                    <span class="absolute top-3 right-3 bg-white text-dark text-xs font-medium px-3 py-1 rounded-full shadow-sm border border-gold/10">
                                        {{ $contest->platformCategory->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Card body --}}
                            <div class="p-5 flex flex-col flex-1">

                                {{-- Title --}}
                                <h3 class="font-serif text-lg font-bold text-dark mb-1 leading-snug group-hover:text-primary transition-colors">
                                    {{ $contest->title }}
                                </h3>

                                {{-- Description excerpt --}}
                                @if($contest->description)
                                    <p class="text-warm-gray text-sm mb-3 line-clamp-2 leading-relaxed">{{ Str::limit(strip_tags($contest->description), 160) }}</p>
                                @endif

                                {{-- Dates --}}
                                <div class="text-xs text-warm-gray space-y-1 mb-4">
                                    <div>
                                        <i class="fas fa-calendar-alt mr-1.5"></i>
                                        {{ $contest->applications_start_at->isoFormat('D MMM') }} — {{ $contest->applications_end_at->isoFormat('D MMM') }}
                                    </div>
                                    <div>
                                        <i class="fas fa-flag-checkered mr-1.5"></i>
                                        Результаты: {{ $contest->evaluation_end_at->isoFormat('D MMM') }}
                                    </div>
                                </div>

                                {{-- Org row --}}
                                <div class="mt-auto pt-3 border-t border-gold/10 flex items-center gap-2">
                                    <x-org-avatar :organization="$contest->organization" size="xs" />
                                    <span class="text-sm text-dark font-medium truncate flex-1">{{ $contest->organization->name }}</span>
                                    <i class="fas fa-chevron-right text-xs text-warm-gray shrink-0"></i>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                <div>{{ $contests->links() }}</div>

            @else
                <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-trophy text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Конкурсов пока нет</h3>
                    <p class="text-warm-gray">Загляните позже — новые конкурсы появятся здесь.</p>
                </div>
            @endif

        </div>

        {{-- ========== CONTEST MODAL ========== --}}
        <div
            x-show="modal"
            x-cloak
            @click.self="modal = null"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        >
            <div
                @click.stop
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="bg-white rounded-2xl max-w-lg w-full shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden"
            >
                {{-- Cover / header --}}
                <div class="relative h-40 shrink-0 overflow-hidden">
                    <img
                        x-show="modal && modal.cover_image"
                        :src="modal ? (modal.cover_image || '') : ''"
                        class="w-full h-full object-cover"
                        style="display: none;"
                        alt=""
                    >
                    <div
                        x-show="!modal || !modal.cover_image"
                        class="w-full h-full gradient-gold flex items-center justify-center"
                    >
                        <i class="fas fa-award text-white/25 text-6xl"></i>
                    </div>

                    {{-- Close button --}}
                    <button
                        @click="modal = null"
                        class="absolute top-3 right-3 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center text-dark shadow-sm transition-colors"
                    >
                        <i class="fas fa-times text-sm"></i>
                    </button>

                    {{-- Category badge --}}
                    <template x-if="modal && modal.category">
                        <span
                            class="absolute bottom-3 left-3 bg-white text-dark text-xs font-medium px-3 py-1 rounded-full shadow-sm border border-gold/10"
                            x-text="modal.category"
                        ></span>
                    </template>
                </div>

                {{-- Scrollable content --}}
                <div class="overflow-y-auto flex-1 p-6">

                    {{-- Title --}}
                    <h2
                        class="font-serif text-2xl font-bold text-dark mb-1 leading-tight"
                        x-text="modal ? modal.title : ''"
                    ></h2>

                    {{-- Org --}}
                    <p
                        class="text-sm text-warm-gray mb-4"
                        x-text="modal ? modal.org_name : ''"
                    ></p>

                    {{-- Description --}}
                    <template x-if="modal && modal.description">
                        <div
                            class="rich-content text-dark text-sm leading-relaxed mb-5"
                            x-html="modal.description"
                        ></div>
                    </template>

                    {{-- Dates --}}
                    <div class="bg-cream rounded-xl p-4 space-y-2 mb-4 text-sm">
                        <template x-if="modal && modal.regulations_url">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-file-alt text-primary w-4 text-center shrink-0"></i>
                                <a :href="modal.regulations_url" target="_blank" rel="noopener noreferrer"
                                    class="font-medium text-primary hover:underline">Положение о конкурсе</a>
                            </div>
                        </template>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-calendar-alt text-primary w-4 text-center shrink-0"></i>
                            <span class="text-warm-gray shrink-0">Приём заявок:</span>
                            <span
                                class="font-medium text-dark"
                                x-text="modal ? (modal.applications_start + ' — ' + modal.applications_end) : ''"
                            ></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-flag-checkered text-primary w-4 text-center shrink-0"></i>
                            <span class="text-warm-gray shrink-0">Результаты:</span>
                            <span
                                class="font-medium text-dark"
                                x-text="modal ? modal.evaluation_end : ''"
                            ></span>
                        </div>
                    </div>

                    {{-- Nominations --}}
                    <template x-if="modal && modal.categories && modal.categories.length">
                        <div class="mb-5">
                            <p class="text-xs font-semibold text-warm-gray uppercase tracking-wider mb-2">Номинации</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="cat in modal.categories" :key="cat">
                                    <span
                                        class="text-xs px-3 py-1 bg-gold/10 text-dark rounded-full border border-gold/20"
                                        x-text="cat"
                                    ></span>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Action buttons --}}
                    <div class="flex gap-3 pt-2">
                        @auth
                            <a
                                :href="modal ? modal.apply_url : '#'"
                                class="flex-1 text-center px-4 py-3 gradient-gold text-dark font-semibold rounded-xl hover:opacity-90 transition-opacity"
                            >
                                <i class="fas fa-paper-plane mr-2"></i>Участвовать
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="flex-1 text-center px-4 py-3 gradient-gold text-dark font-semibold rounded-xl hover:opacity-90 transition-opacity"
                            >
                                <i class="fas fa-paper-plane mr-2"></i>Участвовать
                            </a>
                        @endauth
                        <a
                            :href="modal ? modal.show_url : '#'"
                            class="flex-1 text-center px-4 py-3 border border-primary/20 text-primary rounded-xl hover:bg-primary/5 transition-colors"
                        >
                            Подробнее
                        </a>
                    </div>

                </div>
            </div>
        </div>

    </main>

@endsection
