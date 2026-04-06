<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Выплаты</h2>
                <p class="text-warm-gray text-sm mt-0.5">{{ $organization->name }}</p>
            </div>
            <a href="{{ route('organizations.show', $organization) }}"
                class="text-warm-gray hover:text-primary transition text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Назад
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if(session('status') === 'payout-confirmed')
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                    Получение выплаты подтверждено.
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-700 text-sm">
                    {{ session('warning') }}
                </div>
            @endif

            {{-- Registry entries --}}
            @forelse($registries as $registry)
                <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <span class="font-semibold text-dark">{{ $registry->contest->title ?? '—' }}</span>
                                @if($registry->payment_received)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <i class="fa-solid fa-check mr-1"></i>Подтверждено
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                        Ожидает подтверждения
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="text-warm-gray text-xs mb-0.5">Сумма к выплате</div>
                                    <div class="font-semibold text-dark">{{ number_format($registry->payout_amount, 0, '.', ' ') }} ₽</div>
                                </div>
                                <div>
                                    <div class="text-warm-gray text-xs mb-0.5">Сумма перечисления</div>
                                    <div class="font-semibold text-dark">{{ $registry->transfer_amount ? number_format($registry->transfer_amount, 0, '.', ' ') . ' ₽' : '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-warm-gray text-xs mb-0.5">Документ</div>
                                    @if($registry->transfer_document_number)
                                        <div class="text-dark">№ {{ $registry->transfer_document_number }}
                                            @if($registry->transfer_document_date)
                                                от {{ $registry->transfer_document_date->format('d.m.Y') }}
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-warm-gray">—</div>
                                    @endif
                                </div>
                                <div>
                                    @if($registry->transfer_document_path)
                                        <div class="text-warm-gray text-xs mb-0.5">Файл</div>
                                        <a href="{{ Storage::url($registry->transfer_document_path) }}" target="_blank"
                                            class="text-primary text-sm hover:underline">
                                            <i class="fas fa-file-pdf mr-1"></i>Открыть PDF
                                        </a>
                                    @endif
                                </div>
                            </div>

                            @if($registry->payment_received)
                                <div class="mt-3 text-xs text-warm-gray">
                                    Подтверждено: {{ $registry->payment_received_at?->format('d.m.Y H:i') }}
                                    {{ $registry->receivedBy ? '— ' . $registry->receivedBy->full_name : '' }}
                                </div>
                            @endif
                        </div>

                        {{-- Confirm button --}}
                        @if(!$registry->payment_received && $registry->transfer_document_path)
                            <form action="{{ route('organizations.payouts.confirm', [$organization, $registry]) }}" method="POST"
                                onsubmit="return confirm('Подтвердить получение выплаты?')">
                                @csrf
                                <button type="submit"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                                    <i class="fas fa-check mr-2"></i>Подтвердить получение
                                </button>
                            </form>
                        @elseif(!$registry->payment_received)
                            <div class="text-xs text-warm-gray italic">Ожидание документа перечисления</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-gray-200 text-center py-16 text-warm-gray">
                    <i class="fas fa-file-invoice-dollar text-4xl mb-3 opacity-30"></i>
                    <p>Записей о выплатах пока нет</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
