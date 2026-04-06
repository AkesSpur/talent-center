<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Платежи</h2>
            <p class="text-warm-gray text-sm mt-0.5">Все успешные платежи за участие в конкурсах</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Filters --}}
            <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-dark mb-1">Статус</label>
                        <select name="status" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                            <option value="">Все статусы</option>
                            @foreach(\App\Enums\PaymentStatus::cases() as $s)
                                <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark mb-1">Конкурс</label>
                        <select name="contest_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary focus:border-primary">
                            <option value="">Все конкурсы</option>
                            @foreach($contests as $c)
                                <option value="{{ $c->id }}" @selected(request('contest_id') == $c->id)>{{ $c->title }}</option>
                            @endforeach
                        </select>
                    </div>
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
                    <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-dark hover:bg-gray-50 transition">Сбросить</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                @if($payments->isEmpty())
                    <div class="text-center py-16 text-warm-gray">
                        <i class="fa-solid fa-credit-card text-4xl mb-3 opacity-30"></i>
                        <p>Платежи не найдены</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-cream border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-dark">ID</th>
                                    <th class="px-4 py-3 text-left font-medium text-dark">Участник</th>
                                    <th class="px-4 py-3 text-left font-medium text-dark">Конкурс</th>
                                    <th class="px-4 py-3 text-right font-medium text-dark">Сумма, ₽</th>
                                    <th class="px-4 py-3 text-left font-medium text-dark">Статус</th>
                                    <th class="px-4 py-3 text-left font-medium text-dark">Дата</th>
                                    <th class="px-4 py-3 text-left font-medium text-dark">Заказ T-Bank</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($payments as $payment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-warm-gray">#{{ $payment->id }}</td>
                                        <td class="px-4 py-3">{{ $payment->user->full_name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-dark">{{ $payment->contest->title ?? '—' }}</div>
                                            <div class="text-xs text-warm-gray">{{ $payment->contest->organization->name ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-dark">{{ number_format($payment->amount, 0, '.', ' ') }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payment->status->color() }}">
                                                {{ $payment->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-warm-gray">{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                        <td class="px-4 py-3 text-xs text-warm-gray font-mono">{{ $payment->tbank_order_id }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-t border-gray-100">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
