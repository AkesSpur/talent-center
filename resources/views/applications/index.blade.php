<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Мои заявки</h2>
                <p class="text-warm-gray mt-1">Заявки, поданные вами и вашими участниками</p>
            </div>
            <a href="{{ route('contests.index') }}" class="self-start sm:self-auto px-5 py-2.5 border border-primary/20 text-primary text-sm font-medium rounded-lg hover:bg-primary/5 transition-colors whitespace-nowrap">
                <i class="fas fa-search mr-2"></i>Найти конкурсы
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($applications->count())
                <div x-data="{ tab: 'all' }">

                    {{-- Filter tabs --}}
                    <div class="flex gap-2 mb-4 flex-wrap">
                        <button @click="tab = 'all'"
                            :class="tab === 'all'
                                ? 'bg-primary text-white shadow-sm'
                                : 'border border-primary/20 text-warm-gray hover:text-primary hover:border-primary/40'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            Все
                        </button>
                        <button @click="tab = 'active'"
                            :class="tab === 'active'
                                ? 'bg-primary text-white shadow-sm'
                                : 'border border-primary/20 text-warm-gray hover:text-primary hover:border-primary/40'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            Активные
                        </button>
                        <button @click="tab = 'victories'"
                            :class="tab === 'victories'
                                ? 'gradient-gold text-dark shadow-sm'
                                : 'border border-gold/30 text-warm-gray hover:text-dark hover:border-gold/60'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-trophy mr-1.5"></i>Победы
                        </button>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg border border-gold/10 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm min-w-[640px]">
                                <thead>
                                    <tr class="border-b border-gold/10 text-warm-gray text-xs uppercase tracking-wider">
                                        <th class="text-left px-6 py-3 font-semibold">Конкурс</th>
                                        <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Участник</th>
                                        <th class="text-left px-6 py-3 font-semibold hidden lg:table-cell">Номинация</th>
                                        <th class="text-left px-6 py-3 font-semibold">Статус</th>
                                        <th class="text-left px-6 py-3 font-semibold hidden md:table-cell">Дата подачи</th>
                                        <th class="text-left px-6 py-3 font-semibold">Работа</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gold/10">
                                    @foreach($applications as $application)
                                        @php
                                            $isActive  = in_array($application->status->value, ['new', 'participant', 'payment_pending']);
                                            $isVictory = in_array($application->status->value, ['place_1', 'place_2', 'place_3']);
                                        @endphp
                                        <tr class="hover:bg-cream/50 transition-colors"
                                            x-show="tab === 'all'
                                                || (tab === 'active' && {{ $isActive ? 'true' : 'false' }})
                                                || (tab === 'victories' && {{ $isVictory ? 'true' : 'false' }})">
                                            <td class="px-6 py-4">
                                                <a href="{{ route('contests.show', $application->contest) }}"
                                                    class="font-semibold text-dark hover:text-primary transition-colors block">
                                                    {{ $application->contest->title }}
                                                </a>
                                                <p class="text-xs text-warm-gray mt-0.5">{{ $application->contest->organization->name }}</p>
                                            </td>
                                            <td class="px-6 py-4 hidden md:table-cell text-warm-gray text-sm">
                                                {{ $application->user->full_name }}
                                                @if($application->user_id === auth()->id())
                                                    <span class="text-xs text-primary">(я)</span>
                                                @endif
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
                                            <td class="px-6 py-4 text-sm">
                                                @if($application->status === \App\Enums\ApplicationStatus::PaymentPending)
                                                    <form method="POST" action="{{ route('payments.initiate', $application) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 gradient-gold text-dark text-xs font-semibold rounded-lg hover:opacity-90 transition-opacity whitespace-nowrap">
                                                            <i class="fas fa-credit-card"></i>Оплатить
                                                        </button>
                                                    </form>
                                                @elseif($application->external_link)
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6">{{ $applications->links() }}</div>

                </div>

            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-file-alt text-3xl text-primary"></i>
                    </div>
                    <h3 class="font-serif text-xl font-semibold text-dark mb-2">Заявок пока нет</h3>
                    <p class="text-warm-gray mb-6">Примите участие в конкурсах, чтобы ваши заявки появились здесь.</p>
                    <a href="{{ route('contests.index') }}" class="inline-block px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas fa-search mr-2"></i>Найти конкурсы
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
