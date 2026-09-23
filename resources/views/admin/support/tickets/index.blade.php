<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Поддержка</h2>
                <p class="text-warm-gray mt-1">Обращения пользователей</p>
            </div>
            <div class="flex flex-wrap gap-2">
                {{-- «Настройки поддержки» are admin-only (TZ 4.2) --}}
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.support.categories.index') }}"
                        class="inline-flex items-center px-4 py-2.5 border border-primary/20 text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm">
                        <i class="fas fa-sliders mr-2"></i>Настройки
                    </a>
                @endif
                <a href="{{ route('admin.support.tickets.create') }}"
                    class="inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                    <i class="fas fa-plus mr-2"></i>Создать заявку
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            {{-- Summary --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Every unresolved ticket, split by status so the number matches the table filters --}}
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-5">
                    <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">Открытые</p>
                    <p class="font-serif text-2xl font-bold text-dark">{{ $counts['open'] }}</p>
                    <p class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-warm-gray">
                        @foreach([
                            \App\Enums\SupportTicketStatus::New->value                => 'Новые',
                            \App\Enums\SupportTicketStatus::InProgress->value         => 'В работе',
                            \App\Enums\SupportTicketStatus::NeedsClarification->value => 'Ждут уточнения',
                        ] as $status => $label)
                            <a href="{{ route('admin.support.tickets.index', ['status' => $status]) }}"
                                class="hover:text-primary transition-colors">
                                {{ $label }}: <span class="font-medium text-dark">{{ $counts['byStatus'][$status] ?? 0 }}</span>
                            </a>
                        @endforeach
                    </p>
                </div>
                <a href="{{ route('admin.support.tickets.index', ['overdue' => 1]) }}"
                    class="bg-white rounded-xl shadow-sm border {{ $counts['overdue'] > 0 ? 'border-red-200' : 'border-gold/10' }} p-5 hover:border-primary/30 transition-colors">
                    <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">Просроченные</p>
                    <p class="font-serif text-2xl font-bold {{ $counts['overdue'] > 0 ? 'text-red-600' : 'text-dark' }}">{{ $counts['overdue'] }}</p>
                </a>
                <a href="{{ route('admin.support.tickets.index', ['assignee' => auth()->id()]) }}"
                    class="bg-white rounded-xl shadow-sm border border-gold/10 p-5 hover:border-primary/30 transition-colors">
                    <p class="text-warm-gray text-xs uppercase tracking-wider mb-1">Мои заявки</p>
                    <p class="font-serif text-2xl font-bold text-dark">{{ $counts['mine'] }}</p>
                </a>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.support.tickets.index') }}"
                class="bg-white rounded-xl shadow-sm border border-gold/10 p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="lg:col-span-1">
                    <label for="f-status" class="block text-xs font-medium text-warm-gray mb-1">Статус</label>
                    <select id="f-status" name="status" class="w-full px-3 py-2 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Все</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="f-category" class="block text-xs font-medium text-warm-gray mb-1">Категория</label>
                    <select id="f-category" name="category" class="w-full px-3 py-2 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Все</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-1">
                    <label for="f-assignee" class="block text-xs font-medium text-warm-gray mb-1">Ответственный</label>
                    <select id="f-assignee" name="assignee" class="w-full px-3 py-2 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Все</option>
                        <option value="none" @selected(request('assignee') === 'none')>Не назначен</option>
                        @foreach($operators as $operator)
                            <option value="{{ $operator->id }}" @selected((int) request('assignee') === $operator->id)>{{ $operator->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f-from" class="block text-xs font-medium text-warm-gray mb-1">Дата с</label>
                    <input id="f-from" type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
                <div>
                    <label for="f-to" class="block text-xs font-medium text-warm-gray mb-1">по</label>
                    <input id="f-to" type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-primary/20 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
                <div class="sm:col-span-2 lg:col-span-6 flex flex-wrap items-center gap-3 pt-1">
                    <button type="submit" class="px-5 py-2 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                        Применить
                    </button>
                    <a href="{{ route('admin.support.tickets.index') }}" class="text-sm text-warm-gray hover:text-primary transition-colors">Сбросить</a>
                    <label class="flex items-center gap-2 text-sm text-dark cursor-pointer ml-auto">
                        <input type="checkbox" name="overdue" value="1" @checked(request()->boolean('overdue'))
                            class="rounded border-primary/30 text-primary">
                        Только просроченные
                    </label>
                </div>
            </form>

            @if($tickets->count())
                @php
                    $sortLink = function (string $column) use ($sort, $direction) {
                        $next = ($sort === $column && $direction === 'desc') ? 'asc' : 'desc';
                        return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next]);
                    };
                    $arrow = fn (string $column) => $sort === $column
                        ? ($direction === 'asc' ? 'fa-arrow-up-short-wide' : 'fa-arrow-down-wide-short')
                        : 'fa-sort';
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-4 py-3 font-semibold w-20">
                                        <a href="{{ $sortLink('id') }}" class="inline-flex items-center gap-1 hover:text-primary transition-colors">
                                            Номер <i class="fas {{ $arrow('id') }} text-[10px]"></i>
                                        </a>
                                    </th>
                                    <th class="text-left px-4 py-3 font-semibold">Тема</th>
                                    <th class="text-left px-4 py-3 font-semibold hidden lg:table-cell">Пользователь</th>
                                    <th class="text-left px-4 py-3 font-semibold hidden md:table-cell">Категория</th>
                                    <th class="text-left px-4 py-3 font-semibold w-40">
                                        <a href="{{ $sortLink('status') }}" class="inline-flex items-center gap-1 hover:text-primary transition-colors">
                                            Статус <i class="fas {{ $arrow('status') }} text-[10px]"></i>
                                        </a>
                                    </th>
                                    <th class="text-left px-4 py-3 font-semibold hidden xl:table-cell w-36">Ответственный</th>
                                    <th class="text-left px-4 py-3 font-semibold hidden sm:table-cell w-36">
                                        <a href="{{ $sortLink('sla_deadline') }}" class="inline-flex items-center gap-1 hover:text-primary transition-colors">
                                            Срок <i class="fas {{ $arrow('sla_deadline') }} text-[10px]"></i>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold/10">
                                @foreach($tickets as $ticket)
                                    @php $unread = $ticket->hasUnreadForStaff(); @endphp
                                    <tr class="hover:bg-cream/30 transition-colors">
                                        <td class="px-4 py-3">
                                            @if($unread)
                                                <span class="mr-1 inline-block h-2 w-2 rounded-full bg-primary align-middle" title="Новое сообщение" aria-hidden="true"></span>
                                                <span class="sr-only">Новое сообщение.</span>
                                            @endif
                                            <a href="{{ route('admin.support.tickets.show', $ticket) }}" class="font-medium text-primary hover:underline">
                                                {{ $ticket->number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.support.tickets.show', $ticket) }}"
                                                class="{{ $unread ? 'font-semibold' : '' }} text-dark hover:text-primary transition-colors">
                                                {{ Str::limit($ticket->subject, 55) }}
                                            </a>
                                            @if($ticket->comments_count)
                                                <span class="ml-1 text-xs text-warm-gray"><i class="fas fa-comment-dots"></i> {{ $ticket->comments_count }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 hidden lg:table-cell text-warm-gray">
                                            {{ $ticket->user?->full_name ?? $ticket->guest_email ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell text-warm-gray">{{ $ticket->category?->name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $ticket->status->color() }}">
                                                {{ $ticket->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 hidden xl:table-cell text-warm-gray">
                                            {{ $ticket->assignee?->full_name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 hidden sm:table-cell">
                                            @if($ticket->isOverdueNow())
                                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-700">
                                                    <i class="fas fa-triangle-exclamation"></i>Просрочена
                                                </span>
                                            @else
                                                <span class="text-warm-gray text-xs">
                                                    {{ $ticket->sla_deadline?->timezone('Europe/Moscow')->format('d.m H:i') ?? '—' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($tickets->hasPages())
                    <div>{{ $tickets->links() }}</div>
                @endif
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-inbox text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Заявок нет</h3>
                    <p class="text-warm-gray">По выбранным фильтрам ничего не найдено.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
