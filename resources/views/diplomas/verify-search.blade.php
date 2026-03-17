@extends('layouts.public')

@section('title', 'Проверить диплом — Талант-центр')

@section('body-class', 'min-h-screen flex flex-col')

@section('head')
<style>
    @keyframes shrink {
        from { width: 100%; }
        to   { width: 0%; }
    }
</style>
@endsection

@section('content')

    {{-- Toast notification --}}
    @if(session('error'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="fixed top-6 right-6 z-50 max-w-sm w-full"
        style="display: none;"
    >
        <div class="bg-white border border-red-200 rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-start gap-3 p-4">
                <div class="w-9 h-9 bg-red-100 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas fa-times-circle text-red-500 text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-dark mb-0.5">Диплом не найден</p>
                    <p class="text-sm text-warm-gray leading-snug">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="shrink-0 text-warm-gray hover:text-dark transition-colors mt-0.5">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="h-1 bg-red-100">
                <div class="h-1 bg-red-400 animate-[shrink_5s_linear_forwards]" style="width:100%"></div>
            </div>
        </div>
    </div>
    @endif

    <!-- ========== HERO ========== -->
    <section class="pattern-bg py-8 sm:py-10 px-4 text-center">
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 gradient-gold rounded-full flex items-center justify-center shadow-md">
                <i class="fas fa-certificate text-white text-2xl"></i>
            </div>
        </div>
        <h2 class="font-serif text-3xl md:text-4xl font-bold text-dark mb-3">
            Проверить диплом
        </h2>
        <p class="text-warm-gray text-md max-w-xl mx-auto">
            Введите номер диплома для проверки подлинности и получения информации о награде
        </p>
    </section>

    <!-- ========== MAIN ========== -->
    <main class="flex-1 py-12 px-4">
        <div class="max-w-lg mx-auto">

            <div class="bg-white rounded-2xl shadow-sm border border-gold/10 p-8">
                <form method="POST" action="{{ route('diplomvtrifi.find') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="diploma_number" class="block text-sm font-semibold text-dark mb-2">
                            <i class="fas fa-hashtag text-gold mr-1.5"></i>Номер диплома
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="diploma_number"
                                name="diploma_number"
                                value="{{ old('diploma_number') }}"
                                placeholder="Например: 1-0000042"
                                class="w-full border rounded-xl px-4 py-3 pr-10 text-dark placeholder-warm-gray focus:outline-none focus:ring-2 focus:ring-gold/40 focus:border-gold transition-colors {{ session('error') || $errors->has('diploma_number') ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }}"
                                autofocus
                            >
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                                <i class="fas fa-search {{ session('error') || $errors->has('diploma_number') ? 'text-red-400' : 'text-warm-gray' }}"></i>
                            </div>
                        </div>
                        @error('diploma_number')
                            <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-warm-gray">
                            <i class="fas fa-info-circle mr-1"></i>Номер диплома указан в нижней части документа
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full gradient-gold text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-search"></i>
                        Найти диплом
                    </button>
                </form>
            </div>

            <!-- Info card -->
            <div class="mt-6 bg-white rounded-2xl border border-gold/10 shadow-sm p-6">
                <h3 class="font-serif font-semibold text-dark mb-3 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-gold"></i>
                    Зачем проверять диплом?
                </h3>
                <ul class="space-y-2 text-sm text-warm-gray">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-gold mt-0.5 shrink-0"></i>
                        Убедитесь в подлинности документа
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-gold mt-0.5 shrink-0"></i>
                        Получите полную информацию о награде и конкурсе
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-gold mt-0.5 shrink-0"></i>
                        Подтвердите данные участника и организатора
                    </li>
                </ul>
            </div>

        </div>
    </main>

@endsection
