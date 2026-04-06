<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-dark">Оплата</h2>
    </x-slot>

    <div class="py-16">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-xmark text-red-600 text-3xl"></i>
            </div>
            <h1 class="font-playfair text-3xl font-bold text-dark mb-3">Оплата не прошла</h1>
            <p class="text-warm-gray mb-6">
                К сожалению, платёж был отменён или произошла ошибка. Ваша заявка сохранена — вы можете попробовать оплатить снова из раздела «Мои заявки».
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('dashboard.applications') }}"
                    class="inline-flex items-center justify-center px-6 py-3 gradient-gold text-dark font-semibold rounded-lg hover:opacity-90 transition">
                    <i class="fas fa-list mr-2"></i>Мои заявки
                </a>
                @if($payment?->application?->contest)
                    <a href="{{ route('contests.show', $payment->application->contest) }}"
                        class="inline-flex items-center justify-center px-6 py-3 border border-primary/30 text-primary font-medium rounded-lg hover:bg-primary/5 transition">
                        <i class="fas fa-arrow-left mr-2"></i>К конкурсу
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
