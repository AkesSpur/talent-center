{{-- Full-size view for conversation images. One per page; any [data-lightbox] link opens it. --}}
<div x-data="supportLightbox"
    @support-lightbox.window="show($event.detail)"
    @keydown.escape.window="close()"
    @keydown.arrow-left.window="step(-1)"
    @keydown.arrow-right.window="step(1)">
    <div x-show="open" x-cloak x-ref="dialog"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @keydown.tab="trapFocus($event)"
        role="dialog" aria-modal="true" :aria-label="current ? 'Просмотр изображения ' + current.name : 'Просмотр изображения'"
        class="fixed inset-0 z-[70] flex flex-col bg-dark/90 backdrop-blur-sm">

        <div class="flex items-center gap-3 px-4 py-3 text-white">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium truncate" :title="current?.name" x-text="current?.name"></p>
                <p class="text-xs text-white/60">
                    <span x-text="current?.size"></span>
                    <span x-show="items.length > 1" x-text="' · ' + (index + 1) + ' из ' + items.length"></span>
                </p>
            </div>
            <a :href="current?.download"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-white/90 hover:bg-white/10 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold/60">
                <i class="fas fa-download" aria-hidden="true"></i><span class="hidden sm:inline">Скачать</span>
            </a>
            <button type="button" x-ref="close" @click="close()" aria-label="Закрыть просмотр"
                class="w-10 h-10 rounded-full text-white/90 hover:bg-white/10 flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold/60">
                <i class="fas fa-xmark text-lg" aria-hidden="true"></i>
            </button>
        </div>

        <div class="relative flex-1 min-h-0 flex items-center justify-center px-4 pb-6 sm:px-16" @click.self="close()">
            <i x-show="loading && !broken" class="fas fa-circle-notch fa-spin text-3xl text-white/70 absolute" aria-hidden="true"></i>

            <div x-show="broken" x-cloak class="text-center text-white/80 text-sm space-y-3">
                <p>Не удалось показать изображение.</p>
                <a :href="current?.download" class="inline-flex items-center gap-2 text-gold hover:underline">
                    <i class="fas fa-download" aria-hidden="true"></i>Скачать файл
                </a>
            </div>

            {{-- x-on: rather than @ — Blade would read "@error" as its own directive --}}
            <img x-ref="image" x-show="!broken" :src="current?.src" :alt="current?.name ?? ''"
                x-on:load="loading = false" x-on:error="loading = false; broken = true"
                class="max-h-full max-w-full object-contain rounded-lg shadow-2xl transition-opacity duration-200"
                :class="loading ? 'opacity-0' : 'opacity-100'">

            <template x-if="items.length > 1">
                <div>
                    <button type="button" @click="step(-1)" aria-label="Предыдущее изображение"
                        class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 text-white hover:bg-white/20 flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold/60">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" @click="step(1)" aria-label="Следующее изображение"
                        class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 text-white hover:bg-white/20 flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gold/60">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
