<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Конкурсы</h2>
            <p class="text-warm-gray mt-1">Все конкурсы платформы — просмотр</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filters --}}
            <form method="GET" action="{{ route('support.contests.index') }}"
                class="bg-white rounded-xl shadow-sm border border-gold/10 p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-dark mb-1">Поиск</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Название конкурса..."
                        class="px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm min-w-[200px]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-dark mb-1">Статус</label>
                    <select name="status"
                        class="px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <option value="">Все статусы</option>
                        @foreach(\App\Enums\ContestStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-dark mb-1">Категория</label>
                    <select name="category"
                        class="px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <option value="">Все категории</option>
                        @foreach($platformCategories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 text-sm">Применить</button>
                @if(request('search') || request('status') || request('category'))
                    <a href="{{ route('support.contests.index') }}"
                        class="px-4 py-2 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 text-sm">Сбросить</a>
                @endif
            </form>

            @if($contests->count())
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[700px]">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-6 py-3 font-semibold">Конкурс</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Организация</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden lg:table-cell">Категория</th>
                                    <th class="text-left px-6 py-3 font-semibold">Статус</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden lg:table-cell">Даты</th>
                                    <th class="text-right px-6 py-3 font-semibold">Детали</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold/10">
                                @foreach($contests as $contest)
                                    <tr class="hover:bg-cream/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                @if($contest->cover_image)
                                                    <img src="{{ asset('storage/' . $contest->cover_image) }}"
                                                        class="w-12 h-9 object-cover rounded-lg shrink-0" alt="">
                                                @else
                                                    <div class="w-12 h-9 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                                        <i class="fas fa-trophy text-primary text-sm"></i>
                                                    </div>
                                                @endif
                                                <span class="font-medium text-dark truncate">{{ $contest->title }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell text-dark text-sm">{{ $contest->organization->name }}</td>
                                        <td class="px-6 py-4 hidden lg:table-cell text-warm-gray text-sm">{{ $contest->platformCategory?->name ?? '—' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $contest->status->color() }}">
                                                {{ $contest->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 hidden lg:table-cell text-warm-gray text-xs">
                                            <div>{{ $contest->applications_start_at->format('d.m.Y') }}</div>
                                            <div>— {{ $contest->applications_end_at->format('d.m.Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('support.contests.show', $contest) }}"
                                                class="px-3 py-1.5 border border-primary/20 text-primary text-xs rounded-lg hover:bg-primary/5 transition-colors">
                                                <i class="fas fa-eye mr-1"></i>Просмотр
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>{{ $contests->links() }}</div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-trophy text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Конкурсы не найдены</h3>
                    <p class="text-warm-gray">Попробуйте изменить параметры поиска.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
