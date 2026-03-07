@props([
    'name',
    'title' => 'Подтверждение',
    'message' => 'Вы уверены?',
    'icon' => 'fa-question-circle',
    'iconColor' => 'text-primary',
    'iconBg' => 'bg-primary/10',
    'confirmText' => 'Подтвердить',
    'confirmClass' => 'gradient-gold text-dark',
    'cancelText' => 'Отмена',
    'method' => 'POST',
])

<div x-data="{ open: false, action: '', loading: false }"
     @confirm-{{ $name }}.window="action = $event.detail.action; open = true"
     x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-on:keydown.escape.window="if (!loading) open = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">

    {{-- Loading overlay --}}
    <div x-show="loading"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-10 flex flex-col items-center justify-center bg-dark/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl px-10 py-8 flex flex-col items-center gap-4">
            <div class="w-14 h-14 rounded-full border-4 border-gold/30 border-t-gold animate-spin"></div>
            <p class="font-serif text-dark font-semibold text-lg">Обработка...</p>
            <p class="text-warm-gray text-sm text-center max-w-[200px]">Пожалуйста, не закрывайте страницу</p>
        </div>
    </div>

    {{-- Backdrop --}}
    <div x-show="!loading" class="fixed inset-0 bg-dark/40 backdrop-blur-sm" @click="open = false"></div>

    {{-- Dialog --}}
    <div x-show="!loading"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.stop>

        <div class="p-6 text-center">
            {{-- Icon --}}
            <div class="w-14 h-14 {{ $iconBg }} rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas {{ $icon }} text-2xl {{ $iconColor }}"></i>
            </div>

            {{-- Title --}}
            <h3 class="font-serif text-lg font-bold text-dark mb-2">{{ $title }}</h3>

            {{-- Message --}}
            <p class="text-warm-gray text-sm mb-6">{{ $message }}</p>

            {{-- Actions --}}
            <form :action="action" method="POST" class="flex items-center justify-center gap-3"
                  @submit="loading = true">
                @csrf
                @if(strtoupper($method) !== 'POST')
                    @method($method)
                @endif

                <button type="button" @click="open = false"
                    class="px-5 py-2.5 text-warm-gray hover:text-dark border border-primary/20 rounded-lg text-sm transition-colors font-medium">
                    {{ $cancelText }}
                </button>
                {{ $slot }}

                <button type="submit"
                    class="px-5 py-2.5 {{ $confirmClass }} font-semibold rounded-lg hover:opacity-90 transition-opacity text-sm">
                    {{ $confirmText }}
                </button>
            </form>
        </div>
    </div>
</div>
