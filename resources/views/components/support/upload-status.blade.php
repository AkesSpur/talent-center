{{-- Upload progress while sending, and whatever stopped a send. Inside <x-support.upload-form>. --}}
<div {{ $attributes->class(['space-y-2']) }}>
    <div x-show="sending && items.length" x-cloak class="rounded-lg border border-gold/20 bg-cream/60 px-4 py-3">
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 mb-2">
            <p class="text-sm font-medium text-dark" x-text="progressLabel"></p>
            <p class="text-xs text-warm-gray tabular-nums" x-text="progressDetail"></p>
        </div>
        <div class="h-2 rounded-full bg-primary/10 overflow-hidden" role="progressbar" aria-label="Загрузка файлов"
            aria-valuemin="0" aria-valuemax="100" :aria-valuenow="progress">
            <div class="h-full rounded-full gradient-gold transition-[width] duration-300 ease-out"
                :style="{ width: progress + '%' }"></div>
        </div>
        <button type="button" x-show="phase === 'uploading'" @click="cancel()"
            class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-primary hover:underline">
            <i class="fas fa-xmark" aria-hidden="true"></i>Отменить отправку
        </button>
    </div>

    <p x-show="formError" x-cloak role="alert"
        class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <i class="fas fa-circle-exclamation mt-0.5" aria-hidden="true"></i><span x-text="formError"></span>
    </p>
    <p x-show="formNotice" x-cloak role="status"
        class="flex items-start gap-2 rounded-lg border border-gold/20 bg-cream px-4 py-3 text-sm text-dark">
        <i class="fas fa-circle-info mt-0.5 text-primary/60" aria-hidden="true"></i><span x-text="formNotice"></span>
    </p>
</div>
