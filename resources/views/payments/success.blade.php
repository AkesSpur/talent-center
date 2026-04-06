<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Оплата</h2>
    </x-slot>

    <div class="py-16">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 text-3xl"></i>
            </div>
            <h1 class="font-playfair text-3xl font-bold text-dark mb-3">Оплата прошла успешно!</h1>
            @if($payment)
                <p class="text-warm-gray mb-2">
                    Сумма оргвзноса <span class="font-semibold text-dark">{{ number_format($payment->amount, 0, '.', ' ') }} ₽</span> получена.
                </p>
                <p class="text-warm-gray mb-6">
                    Ваша заявка на конкурс <span class="font-semibold text-dark">«{{ $payment->contest->title ?? '' }}»</span> принята.
                </p>
            @else
                <p class="text-warm-gray mb-6">Ваш платёж обрабатывается. Заявка появится в списке в течение нескольких минут.</p>
            @endif
            <a href="{{ route('dashboard.applications') }}"
                class="inline-flex items-center px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition">
                <i class="fas fa-list mr-2"></i>Мои заявки
            </a>
        </div>
    </div>
</x-app-layout>
