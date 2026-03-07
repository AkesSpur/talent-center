<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('admin.contests.index') }}" class="text-warm-gray hover:text-primary transition-colors text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Конкурсы
                    </a>
                </div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">{{ $contest->title }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $contest->status->color() }}">
                        {{ $contest->status->label() }}
                    </span>
                    <span class="text-warm-gray text-sm">· {{ $contest->organization->name }}</span>
                </div>
            </div>
            <a href="{{ route('contests.edit', $contest) }}"
                class="inline-flex items-center px-4 py-2 border border-primary/30 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 transition-colors shrink-0">
                <i class="fas fa-pen mr-1.5"></i>Редактировать
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.contests.applications', $contest) }}"
                class="bg-white rounded-xl shadow-sm border border-gold/10 p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-dark mb-1">Статус заявки</label>
                    <select name="status"
                        class="px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                        <option value="">Все статусы</option>
                        @foreach(\App\Enums\ApplicationStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($contest->categories->count())
                    <div>
                        <label class="block text-xs font-medium text-dark mb-1">Номинация</label>
                        <select name="category"
                            class="px-3 py-2 border border-primary/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 text-sm">
                            <option value="">Все номинации</option>
                            @foreach($contest->categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="px-4 py-2 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 text-sm">Применить</button>
                @if(request('status') || request('category'))
                    <a href="{{ route('admin.contests.applications', $contest) }}"
                        class="px-4 py-2 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 text-sm">Сбросить</a>
                @endif

                <div class="ml-auto text-sm text-warm-gray self-center">
                    Всего заявок: <span class="font-semibold text-dark">{{ $applications->total() }}</span>
                </div>
            </form>

            @if($applications->count())
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[700px]" x-data="{ expanded: null }">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-6 py-3 font-semibold">Участник</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden lg:table-cell">Номинация</th>
                                    <th class="text-left px-6 py-3 font-semibold">Статус</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Дата</th>
                                    <th class="text-left px-6 py-3 font-semibold">Работа</th>
                                    <th class="px-6 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($applications as $application)
                                    <tr class="border-b border-gold/5 hover:bg-cream/30 transition-colors cursor-pointer"
                                        @click="expanded = (expanded === {{ $application->id }}) ? null : {{ $application->id }}">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <x-user-avatar :user="$application->user" size="xs" />
                                                <div class="min-w-0">
                                                    <p class="font-medium text-dark truncate">{{ $application->user->full_name }}</p>
                                                    <p class="text-xs text-warm-gray truncate">{{ $application->user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 hidden lg:table-cell text-warm-gray text-sm">
                                            {{ $application->category?->name ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $application->status->color() }}">
                                                {{ $application->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell text-warm-gray text-sm">
                                            {{ $application->created_at->format('d.m.Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm" @click.stop>
                                            @if($application->external_link)
                                                <a href="{{ $application->external_link }}" target="_blank" rel="noopener noreferrer"
                                                    class="text-primary hover:underline">
                                                    <i class="fas fa-external-link-alt mr-1"></i>Ссылка
                                                </a>
                                            @elseif($application->file_path)
                                                <a href="{{ asset('storage/' . $application->file_path) }}" target="_blank"
                                                    class="text-primary hover:underline">
                                                    <i class="fas fa-file mr-1"></i>Файл
                                                </a>
                                            @else
                                                <span class="text-warm-gray">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <i class="fas fa-chevron-down text-warm-gray text-xs transition-transform duration-200"
                                                :class="expanded === {{ $application->id }} ? 'rotate-180' : ''"></i>
                                        </td>
                                    </tr>
                                    {{-- Expanded stats row --}}
                                    <tr x-show="expanded === {{ $application->id }}" x-cloak
                                        class="border-b border-gold/10 bg-cream/30">
                                        <td colspan="6" class="px-6 py-5">
                                            <div class="flex flex-col sm:flex-row gap-6">
                                                {{-- Participant profile --}}
                                                <div class="flex items-start gap-4 min-w-0 flex-1">
                                                    <x-user-avatar :user="$application->user" size="md" />
                                                    <div class="space-y-1 min-w-0">
                                                        <p class="font-semibold text-dark">{{ $application->user->full_name }}</p>
                                                        <p class="text-sm text-warm-gray"><i class="fas fa-envelope mr-1.5 w-4 text-center"></i>{{ $application->user->email }}</p>
                                                        @if($application->user->phone)
                                                            <p class="text-sm text-warm-gray"><i class="fas fa-phone mr-1.5 w-4 text-center"></i>{{ $application->user->phone }}</p>
                                                        @endif
                                                        @if($application->user->birth_date)
                                                            <p class="text-sm text-warm-gray"><i class="fas fa-birthday-cake mr-1.5 w-4 text-center"></i>{{ $application->user->birth_date->format('d.m.Y') }}</p>
                                                        @endif
                                                        @if($application->user->city)
                                                            <p class="text-sm text-warm-gray"><i class="fas fa-map-marker-alt mr-1.5 w-4 text-center"></i>{{ $application->user->city }}</p>
                                                        @endif
                                                        @if($application->user->organization)
                                                            <p class="text-sm text-warm-gray"><i class="fas fa-school mr-1.5 w-4 text-center"></i>{{ $application->user->organization }}</p>
                                                        @endif
                                                        @if($application->user->group)
                                                            <p class="text-sm text-warm-gray"><i class="fas fa-users mr-1.5 w-4 text-center"></i>{{ $application->user->group }}</p>
                                                        @endif
                                                        @if($application->user->education)
                                                            <p class="text-sm text-warm-gray"><i class="fas fa-graduation-cap mr-1.5 w-4 text-center"></i>{{ $application->user->education }}</p>
                                                        @endif
                                                        @if($application->user->bio)
                                                            <p class="text-sm text-dark mt-2 max-w-lg">{{ $application->user->bio }}</p>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Submission preview --}}
                                                @if($application->file_path && $application->file_type?->value === 'image')
                                                    <div class="shrink-0">
                                                        <p class="text-xs text-warm-gray mb-2 font-medium uppercase tracking-wider">Работа</p>
                                                        <a href="{{ asset('storage/' . $application->file_path) }}" target="_blank">
                                                            <img src="{{ asset('storage/' . $application->file_path) }}"
                                                                class="max-h-48 max-w-xs rounded-lg object-cover border border-gold/10 hover:opacity-90 transition-opacity" alt="Работа участника">
                                                        </a>
                                                    </div>
                                                @elseif($application->file_path)
                                                    <div class="shrink-0">
                                                        <p class="text-xs text-warm-gray mb-2 font-medium uppercase tracking-wider">Работа</p>
                                                        <a href="{{ asset('storage/' . $application->file_path) }}" target="_blank"
                                                            class="inline-flex items-center gap-2 px-4 py-3 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                                                            <i class="fas fa-file-alt text-lg"></i>
                                                            <span class="text-sm font-medium">Скачать файл</span>
                                                        </a>
                                                    </div>
                                                @elseif($application->external_link)
                                                    <div class="shrink-0">
                                                        <p class="text-xs text-warm-gray mb-2 font-medium uppercase tracking-wider">Работа</p>
                                                        <a href="{{ $application->external_link }}" target="_blank" rel="noopener noreferrer"
                                                            class="inline-flex items-center gap-2 px-4 py-3 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors">
                                                            <i class="fas fa-external-link-alt text-lg"></i>
                                                            <span class="text-sm font-medium">Открыть ссылку</span>
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
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
                        <i class="fas fa-inbox text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Заявок нет</h3>
                    <p class="text-warm-gray">
                        @if(request('status') || request('category'))
                            Попробуйте изменить параметры фильтрации.
                        @else
                            Участники ещё не подавали заявки на этот конкурс.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
