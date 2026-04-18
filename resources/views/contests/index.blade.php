@extends('layouts.public')

@section('title', 'Конкурсы — Талант-центр')
@section('description', 'Найдите конкурс по своим интересам и участвуйте в творческих состязаниях. Подайте заявку онлайн.')

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
            check(title, catId) {
                const ms = this.search === '' || title.toLowerCase().includes(this.search.toLowerCase());
                const mc = this.category === '' || String(catId) === this.category;
                return ms && mc;
            }
        }"
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

                {{-- Sort --}}
                <select
                    onchange="window.location.href='{{ route('contests.index') }}?sort=' + this.value"
                    class="px-3 py-2.5 border border-primary/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm bg-cream/50 text-dark"
                >
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>По новизне</option>
                    <option value="deadline" {{ $sort === 'deadline' ? 'selected' : '' }}>По дедлайну</option>
                </select>
            </div>

            {{-- Contest grid --}}
            @if($contests->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($contests as $contest)
                        <a
                            href="{{ route('contests.show', $contest) }}"
                            x-show="check({{ Js::from($contest->title) }}, '{{ $contest->platform_category_id ?? '' }}')"
                            class="group bg-white rounded-2xl shadow-sm border border-gold/10 hover-lift flex flex-col overflow-hidden"
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
                                        {{ $contest->applications_start_at->isoFormat('D MMM') }}
                                        @if($contest->applications_end_at)
                                            — {{ $contest->applications_end_at->isoFormat('D MMM') }}
                                        @else
                                            — бессрочно
                                        @endif
                                    </div>
                                    @if($contest->evaluation_end_at)
                                        <div>
                                            <i class="fas fa-flag-checkered mr-1.5"></i>
                                            Результаты: {{ $contest->evaluation_end_at->isoFormat('D MMM') }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Org row --}}
                                <div class="mt-auto pt-3 border-t border-gold/10 flex items-center gap-2">
                                    <x-org-avatar :organization="$contest->organization" size="xs" />
                                    <span class="text-sm text-dark font-medium truncate flex-1">{{ $contest->organization->name }}</span>
                                    <i class="fas fa-chevron-right text-xs text-warm-gray shrink-0"></i>
                                </div>

                            </div>
                        </a>
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

    </main>

@endsection
