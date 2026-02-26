<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Все заявки</h2>
                <p class="text-warm-gray mt-1">
                    Управление заявками платформы
                    @if($applications->total())
                        · <span class="font-medium">{{ $applications->total() }}</span> записей
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.applications.index') }}"
                class="bg-white rounded-xl shadow-sm border border-gold/10 p-4 flex flex-wrap gap-3 items-end">

                <div>
                    <label class="block text-xs font-medium text-dark mb-1">Участник</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Фамилия, имя, email..."
                        class="px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm min-w-[200px]">
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark mb-1">Статус</label>
                    <select name="status"
                        class="px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <option value="">Все статусы</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-dark mb-1">Конкурс</label>
                    <select name="contest"
                        class="px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm min-w-[200px]">
                        <option value="">Все конкурсы</option>
                        @foreach($contests as $contest)
                            <option value="{{ $contest->id }}" {{ request('contest') == $contest->id ? 'selected' : '' }}>
                                {{ $contest->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 text-sm">
                    Применить
                </button>

                @if(request('search') || request('status') || request('contest'))
                    <a href="{{ route('admin.applications.index') }}"
                        class="px-4 py-2 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 text-sm">
                        Сбросить
                    </a>
                @endif
            </form>

            @if($applications->count())
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[900px]">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-6 py-3 font-semibold">Участник</th>
                                    <th class="text-left px-6 py-3 font-semibold">Конкурс</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden lg:table-cell">Номинация</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Дата подачи</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden xl:table-cell">Файл</th>
                                    <th class="text-left px-6 py-3 font-semibold">Статус</th>
                                    <th class="text-right px-6 py-3 font-semibold">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold/10">
                                @foreach($applications as $application)
                                    <tr class="hover:bg-cream/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <x-user-avatar :user="$application->user" size="sm" />
                                                <div class="min-w-0">
                                                    <div class="font-medium text-dark truncate max-w-[160px]">
                                                        {{ $application->user->last_name }} {{ $application->user->first_name }}
                                                    </div>
                                                    <div class="text-xs text-warm-gray truncate max-w-[160px]">
                                                        {{ $application->user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('contests.show', $application->contest) }}"
                                                class="font-medium text-dark hover:text-primary transition-colors block truncate max-w-[180px]">
                                                {{ $application->contest->title }}
                                            </a>
                                            <div class="text-xs text-warm-gray truncate max-w-[180px]">
                                                {{ $application->contest->organization->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 hidden lg:table-cell text-warm-gray text-sm">
                                            {{ $application->category?->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell text-warm-gray text-xs">
                                            {{ $application->created_at->format('d.m.Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 hidden xl:table-cell text-warm-gray text-xs">
                                            @if($application->external_link)
                                                <a href="{{ $application->external_link }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-1 text-primary hover:underline">
                                                    <i class="fas fa-link"></i> Ссылка
                                                </a>
                                            @elseif($application->file_path)
                                                <a href="{{ asset('storage/' . $application->file_path) }}" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-1 text-primary hover:underline">
                                                    <i class="fas fa-file"></i> Файл
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $application->status->color() }}">
                                                {{ $application->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.contests.applications', $application->contest) }}"
                                                class="px-2.5 py-1.5 border border-primary/20 text-primary text-xs rounded-lg hover:bg-primary/5 transition-colors" title="Перейти к заявкам конкурса">
                                                <i class="fas fa-inbox mr-1"></i>Конкурс
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>{{ $applications->links() }}</div>

            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-file-alt text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Заявки не найдены</h3>
                    <p class="text-warm-gray">
                        @if(request()->hasAny(['search','status','contest']))
                            Попробуйте изменить параметры поиска.
                        @else
                            Заявок на платформе пока нет.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
