<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Реестр выплат организациям</h2>
    </x-slot>

    <div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ createModal: false, editId: null }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-playfair text-3xl font-bold text-dark">Реестр выплат организациям</h1>
            <p class="text-warm-gray mt-1">Управление выплатами организациям за проведённые конкурсы</p>
        </div>
        <button @click="createModal = true"
            class="px-4 py-2 gradient-gold text-white rounded-lg text-sm font-medium hover-lift transition">
            <i class="fa-solid fa-plus mr-2"></i>Создать запись
        </button>
    </div>

    {{-- Flash messages --}}
    @if(session('status') === 'payout-registry-created')
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">Запись реестра создана.</div>
    @elseif(session('status') === 'payout-registry-updated')
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">Данные обновлены.</div>
    @elseif(session('status') === 'payout-document-uploaded')
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">Документ загружен.</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-dark mb-1">Организация</label>
                <select name="organization_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                    <option value="">Все организации</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" @selected(request('organization_id') == $org->id)>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90 transition">Применить</button>
            <a href="{{ route('admin.payout-registries.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-dark hover:bg-gray-50 transition">Сбросить</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="space-y-4">
        @forelse($registries as $registry)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-1">
                            <span class="font-semibold text-dark">{{ $registry->contest->title ?? '—' }}</span>
                            @if($registry->payment_received)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <i class="fa-solid fa-check mr-1"></i>Получено подтверждение
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-warm-gray">{{ $registry->organization->name ?? '—' }}</div>

                        <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <div class="text-warm-gray text-xs mb-0.5">Сумма к выплате</div>
                                <div class="font-semibold text-dark">{{ number_format($registry->payout_amount, 0, '.', ' ') }} ₽</div>
                            </div>
                            <div>
                                <div class="text-warm-gray text-xs mb-0.5">Сумма перечисления</div>
                                <div class="font-semibold text-dark">{{ $registry->transfer_amount ? number_format($registry->transfer_amount, 0, '.', ' ') . ' ₽' : '—' }}</div>
                            </div>
                            <div>
                                <div class="text-warm-gray text-xs mb-0.5">Номер документа</div>
                                <div class="text-dark">{{ $registry->transfer_document_number ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-warm-gray text-xs mb-0.5">Дата документа</div>
                                <div class="text-dark">{{ $registry->transfer_document_date ? $registry->transfer_document_date->format('d.m.Y') : '—' }}</div>
                            </div>
                        </div>

                        @if($registry->transfer_document_path)
                            <div class="mt-2">
                                <a href="{{ Storage::url($registry->transfer_document_path) }}" target="_blank"
                                    class="text-primary text-sm hover:underline">
                                    <i class="fa-solid fa-file-pdf mr-1"></i>Документ перечисления
                                </a>
                            </div>
                        @endif

                        @if($registry->payment_received)
                            <div class="mt-2 text-xs text-warm-gray">
                                Подтверждено: {{ $registry->payment_received_at?->format('d.m.Y H:i') }}
                                {{ $registry->receivedBy ? '— ' . $registry->receivedBy->full_name : '' }}
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col gap-2 min-w-[200px]" x-data="{ uploadOpen: false, editOpen: false }">
                        {{-- Edit transfer details --}}
                        <button @click="editOpen = !editOpen"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-dark hover:bg-gray-50 transition text-left">
                            <i class="fa-solid fa-pen-to-square mr-2"></i>Данные перечисления
                        </button>
                        <div x-show="editOpen" x-cloak class="p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <form action="{{ route('admin.payout-registries.update', $registry) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="space-y-2">
                                    <div>
                                        <label class="block text-xs text-warm-gray mb-0.5">Сумма перечисления, ₽</label>
                                        <input type="number" name="transfer_amount" value="{{ $registry->transfer_amount }}"
                                            class="no-spinner w-full border-gray-300 rounded text-sm focus:ring-primary focus:border-primary" min="0">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-warm-gray mb-0.5">Номер документа</label>
                                        <input type="text" name="transfer_document_number" value="{{ $registry->transfer_document_number }}"
                                            class="w-full border-gray-300 rounded text-sm focus:ring-primary focus:border-primary">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-warm-gray mb-0.5">Дата документа</label>
                                        <input type="date" name="transfer_document_date"
                                            value="{{ $registry->transfer_document_date?->format('Y-m-d') }}"
                                            class="w-full border-gray-300 rounded text-sm focus:ring-primary focus:border-primary">
                                    </div>
                                    <button type="submit" class="w-full px-3 py-1.5 bg-primary text-white rounded text-sm hover:bg-primary/90 transition">Сохранить</button>
                                </div>
                            </form>
                        </div>

                        {{-- Upload document --}}
                        <button @click="uploadOpen = !uploadOpen"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-dark hover:bg-gray-50 transition text-left">
                            <i class="fa-solid fa-upload mr-2"></i>Загрузить документ
                        </button>
                        <div x-show="uploadOpen" x-cloak class="p-3 border border-gray-200 rounded-lg bg-gray-50">
                            <form action="{{ route('admin.payout-registries.upload-document', $registry) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="document" accept=".pdf" required
                                    class="w-full text-sm text-warm-gray mb-2 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-cream file:text-dark hover:file:bg-gray-200">
                                <button type="submit" class="w-full px-3 py-1.5 bg-primary text-white rounded text-sm hover:bg-primary/90 transition">Загрузить PDF</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 text-center py-16 text-warm-gray">
                <i class="fa-solid fa-file-invoice-dollar text-4xl mb-3 opacity-30"></i>
                <p>Записей реестра нет</p>
            </div>
        @endforelse

        {{ $registries->links() }}
    </div>

    {{-- Create modal --}}
    <div x-show="createModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @click.self="createModal = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="font-playfair text-xl font-bold text-dark mb-4">Создать запись реестра</h3>
            <form action="{{ route('admin.payout-registries.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-dark mb-1">Конкурс <span class="text-red-500">*</span></label>
                    <select name="contest_id" required
                        class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                        <option value="">Выберите конкурс...</option>
                        @php
                            $paidContests = \App\Models\Contest::where('is_paid', true)
                                ->whereDoesntHave('payoutRegistry')
                                ->orderBy('title')
                                ->get(['id', 'title']);
                        @endphp
                        @foreach($paidContests as $c)
                            <option value="{{ $c->id }}">{{ $c->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 gradient-gold text-white rounded-lg text-sm font-medium hover-lift transition">
                        Создать
                    </button>
                    <button type="button" @click="createModal = false"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-dark hover:bg-gray-50 transition">
                        Отмена
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    </div>
</x-app-layout>
