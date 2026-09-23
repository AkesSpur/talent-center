<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Поддержка</h2>
                <p class="text-warm-gray mt-1">Ваши обращения в службу поддержки</p>
            </div>
            <a href="{{ route('tickets.create') }}"
                class="inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                <i class="fas fa-plus mr-2"></i>Создать заявку
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-notify />

            @if($totalCount > 0)
                {{-- Open / all switch --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('tickets.index') }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ !$showAll ? 'bg-primary text-white' : 'border border-primary/20 text-warm-gray hover:bg-primary/5' }}">
                        Открытые ({{ $openCount }})
                    </a>
                    <a href="{{ route('tickets.index', ['all' => 1]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $showAll ? 'bg-primary text-white' : 'border border-primary/20 text-warm-gray hover:bg-primary/5' }}">
                        Показать все ({{ $totalCount }})
                    </a>
                </div>
            @endif

            @if($tickets->count())
                <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                    <th class="text-left px-6 py-3 font-semibold w-24">Номер</th>
                                    <th class="text-left px-6 py-3 font-semibold">Тема</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Категория</th>
                                    <th class="text-left px-6 py-3 font-semibold w-44">Статус</th>
                                    <th class="text-left px-6 py-3 font-semibold hidden sm:table-cell w-32">Создана</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gold/10">
                                @foreach($tickets as $ticket)
                                    @php $unread = $ticket->hasUnreadForOwner(); @endphp
                                    <tr class="hover:bg-cream/30 transition-colors cursor-pointer"
                                        onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                                        <td class="px-6 py-4 font-medium text-primary whitespace-nowrap">
                                            @if($unread)
                                                <span class="mr-1.5 inline-block h-2 w-2 rounded-full bg-primary align-middle" title="Новое сообщение" aria-hidden="true"></span>
                                                <span class="sr-only">Новое сообщение.</span>
                                            @endif
                                            {{ $ticket->number }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('tickets.show', $ticket) }}"
                                                class="{{ $unread ? 'font-semibold' : 'font-medium' }} text-dark hover:text-primary transition-colors">
                                                {{ Str::limit($ticket->subject, 60) }}
                                            </a>
                                            @if($unread)
                                                <p class="text-xs font-medium text-primary mt-0.5">
                                                    <i class="fas fa-envelope mr-1" aria-hidden="true"></i>Новое сообщение от поддержки
                                                </p>
                                            @elseif($ticket->status === \App\Enums\SupportTicketStatus::NeedsClarification)
                                                <p class="text-xs text-orange-600 mt-0.5">
                                                    <i class="fas fa-circle-exclamation mr-1" aria-hidden="true"></i>Нужен ваш ответ
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell text-warm-gray">{{ $ticket->category?->name ?? '—' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $ticket->status->color() }}">
                                                {{ $ticket->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 hidden sm:table-cell text-warm-gray">{{ $ticket->created_at->timezone('Europe/Moscow')->format('d.m.Y') }}</td>
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
                        <i class="fas fa-headset text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">
                        {{ $totalCount > 0 ? 'Открытых заявок нет' : 'У вас пока нет обращений' }}
                    </h3>
                    <p class="text-warm-gray mb-6 max-w-md mx-auto">
                        {{ $totalCount > 0
                            ? 'Все ваши заявки решены. Нажмите «Показать все», чтобы посмотреть историю.'
                            : 'Если у вас возник вопрос по конкурсу, оплате или работе платформы — создайте заявку, и мы поможем.' }}
                    </p>
                    <a href="{{ route('tickets.create') }}"
                        class="inline-flex items-center px-5 py-2.5 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity active:scale-[0.98] text-sm">
                        <i class="fas fa-plus mr-2"></i>Создать заявку
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
