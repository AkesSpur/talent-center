<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Оценка заявок</h2>
                <p class="text-warm-gray mt-1 text-sm">{{ $organization->name }}</p>
            </div>
            <a href="{{ route('organizations.show', $organization) }}" class="self-start sm:self-auto px-4 py-2 text-warm-gray hover:text-primary transition-colors text-sm">
                <i class="fas fa-arrow-left mr-2"></i>К организации
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Active evaluation contests --}}
            <div>
                <h3 class="font-serif text-lg font-semibold text-dark mb-4">
                    <i class="fas fa-gavel text-gold mr-2"></i>Конкурсы на оценке
                </h3>

                @if($evaluationContests->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($evaluationContests as $contest)
                            @php
                                $progress = $contest->applications_count > 0
                                    ? round(($contest->evaluated_count / $contest->applications_count) * 100)
                                    : 0;
                                $allDone = $contest->applications_count > 0 && $contest->evaluated_count === $contest->applications_count;
                            @endphp
                            <a href="{{ route('evaluation.show', [$organization, $contest]) }}"
                                class="block bg-white rounded-xl p-5 shadow-sm border {{ $allDone ? 'border-green-200' : 'border-gold/10' }} hover-lift transition-colors">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-dark truncate">{{ $contest->title }}</h4>
                                        <p class="text-xs text-warm-gray mt-0.5">
                                            Заявок: {{ $contest->applications_count }}
                                        </p>
                                    </div>
                                    @if($allDone)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 shrink-0">
                                            <i class="fas fa-check mr-1"></i>Готово к завершению
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 shrink-0">
                                            <i class="fas fa-gavel mr-1"></i>В оценке
                                        </span>
                                    @endif
                                </div>

                                {{-- Progress bar --}}
                                <div>
                                    <div class="flex justify-between text-xs text-warm-gray mb-1">
                                        <span>{{ $contest->evaluated_count }} из {{ $contest->applications_count }} оценено</span>
                                        <span class="font-medium {{ $allDone ? 'text-green-600' : 'text-primary' }}">{{ $progress }}%</span>
                                    </div>
                                    <div class="w-full bg-cream-dark rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all duration-500 {{ $allDone ? 'bg-green-500' : 'gradient-gold' }}"
                                            style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-sm p-10 text-center">
                        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-gavel text-2xl text-primary"></i>
                        </div>
                        <h4 class="font-serif text-lg font-semibold text-dark mb-2">Нет конкурсов на оценке</h4>
                        <p class="text-warm-gray text-sm">Конкурсы появятся здесь, когда завершится приём заявок.</p>
                    </div>
                @endif
            </div>

            {{-- Recently archived --}}
            @if($archivedContests->count())
                <div>
                    <h3 class="font-serif text-lg font-semibold text-dark mb-4">
                        <i class="fas fa-archive text-warm-gray mr-2"></i>Завершённые конкурсы
                    </h3>
                    <div class="bg-white rounded-xl shadow-sm border border-gold/10 overflow-hidden">
                        <ul class="divide-y divide-gold/10">
                            @foreach($archivedContests as $contest)
                                <li>
                                    <a href="{{ route('evaluation.show', [$organization, $contest]) }}"
                                        class="flex items-center justify-between px-5 py-3 hover:bg-cream/50 transition-colors gap-3">
                                        <span class="font-medium text-dark text-sm truncate">{{ $contest->title }}</span>
                                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $contest->status->color() }} shrink-0">
                                            {{ $contest->status->label() }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
