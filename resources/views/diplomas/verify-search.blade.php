<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверить диплом — Талант-центр</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream min-h-screen flex flex-col">

    @include('layouts.navigation')

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
            {{-- Progress bar --}}
            <div class="h-1 bg-red-100">
                <div class="h-1 bg-red-400 animate-[shrink_5s_linear_forwards]" style="width:100%"></div>
            </div>
        </div>
    </div>
    @endif

    <main class="flex-1 flex items-center justify-center py-16 px-4">
        <div class="w-full max-w-lg">

            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 gradient-gold rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-award text-white text-3xl"></i>
                </div>
            </div>

            <!-- Heading -->
            <h1 class="font-serif text-3xl font-bold text-dark text-center mb-3">
                Проверить диплом
            </h1>
            <p class="text-warm-gray text-center mb-10 text-base leading-relaxed">
                Введите номер диплома для проверки подлинности и<br>
                получения информации о награде
            </p>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <form method="POST" action="{{ route('diplomvtrifi.find') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="diploma_number" class="block text-sm font-semibold text-dark mb-2">
                            Номер диплома
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="diploma_number"
                                name="diploma_number"
                                value="{{ old('diploma_number') }}"
                                placeholder="Введите номер диплома"
                                class="w-full border rounded-xl px-4 py-3 pr-10 text-dark placeholder-warm-gray focus:outline-none focus:ring-2 focus:ring-gold/40 focus:border-gold transition-colors {{ session('error') || $errors->has('diploma_number') ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }}"
                                autofocus
                            >
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                                <i class="fas fa-hashtag {{ session('error') || $errors->has('diploma_number') ? 'text-red-400' : 'text-warm-gray' }}"></i>
                            </div>
                        </div>
                        @error('diploma_number')
                            <p class="mt-2 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-warm-gray">
                            Номер диплома указан в нижней части документа
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full gradient-gold text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-search"></i>
                        Найти
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-sm text-warm-gray">
                <a href="{{ route('home') }}" class="hover:text-gold transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> На главную
                </a>
            </p>

        </div>
    </main>

    @include('layouts.footer')

    <style>
        @keyframes shrink {
            from { width: 100%; }
            to   { width: 0%; }
        }
    </style>

</body>
</html>
