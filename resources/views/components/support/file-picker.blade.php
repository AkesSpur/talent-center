@props([
    'id' => 'files',
    // Reply boxes get a compact button; the new-ticket form a full drop zone.
    'compact' => false,
])

@php
    $limits = \App\Services\SupportAttachmentService::limitsText();
    $types = \App\Services\SupportAttachmentService::EXTENSIONS_TEXT;
    $accept = collect(\App\Services\SupportAttachmentService::EXTENSIONS)->map(fn ($ext) => '.' . $ext)->implode(',');
@endphp

{{-- Lives inside <x-support.upload-form>, which provides the supportUpload state. --}}
<div {{ $attributes->class(['space-y-3']) }}>
    <input id="{{ $id }}" x-ref="fileInput" type="file" name="files[]" multiple class="hidden"
        accept="{{ $accept }}" @change="picked($event)">

    @if($compact)
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
            <button type="button" x-ref="pickButton" @click="openPicker()" :disabled="sending"
                aria-describedby="{{ $id }}-hint"
                class="inline-flex items-center gap-2 px-4 py-2 border border-primary/20 text-warm-gray rounded-lg hover:border-primary/40 hover:text-primary transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-paperclip" aria-hidden="true"></i>Прикрепить файлы
            </button>
            <span id="{{ $id }}-hint" class="text-xs text-warm-gray">
                {{ $limits }} · можно перетащить файлы сюда или вставить скриншот
            </span>
        </div>
    @else
        <button type="button" x-ref="pickButton" @click="openPicker()" :disabled="sending"
            aria-describedby="{{ $id }}-hint"
            class="w-full flex flex-col items-center justify-center gap-2 px-4 py-8 border-2 border-dashed rounded-lg transition-colors disabled:cursor-not-allowed"
            :class="dragging ? 'border-primary bg-primary/5' : 'border-primary/20 hover:border-primary/40 hover:bg-cream/40'">
            <i class="fas fa-cloud-arrow-up text-2xl text-primary/50" aria-hidden="true"></i>
            <span class="text-sm text-dark">
                <span class="font-medium text-primary">Выберите файлы</span> или перетащите их сюда
            </span>
            <span id="{{ $id }}-hint" class="text-xs text-warm-gray/80">
                {{ $limits }} · {{ $types }} · скриншот можно вставить в описание
            </span>
        </button>
    @endif

    {{-- Picked files: a thumbnail for images, a type icon for the rest --}}
    <ul x-show="items.length" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-2" aria-label="Прикреплённые файлы">
        <template x-for="item in items" :key="item.id">
            <li class="flex items-center gap-3 p-2 pr-2.5 rounded-lg border transition-colors"
                :class="item.error ? 'border-red-300 bg-red-50' : 'border-gold/10 bg-cream'">
                <span class="w-12 h-12 shrink-0 rounded-md overflow-hidden bg-white border border-gold/10 flex items-center justify-center">
                    <template x-if="item.previewUrl">
                        <img :src="item.previewUrl" alt="" class="w-full h-full object-cover" x-on:error="previewFailed(item)">
                    </template>
                    <template x-if="!item.previewUrl">
                        <i class="fas text-lg text-primary/60" :class="item.icon" aria-hidden="true"></i>
                    </template>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm text-dark truncate" :title="item.name" x-text="item.name"></span>
                    <span class="block text-xs" :class="item.error ? 'text-red-600' : 'text-warm-gray'"
                        :data-file-error="item.error ? '' : null" x-text="item.error || item.sizeLabel"></span>
                </span>
                <button type="button" @click="remove(item.id)" :disabled="sending"
                    :aria-label="'Убрать файл ' + item.name" :title="'Убрать ' + item.name"
                    class="w-8 h-8 shrink-0 rounded-full text-warm-gray hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </button>
            </li>
        </template>
    </ul>
    <p x-show="items.length" x-cloak class="text-xs text-warm-gray" x-text="summary"></p>

    {{-- Refused on the spot, before anything was uploaded --}}
    <ul x-show="notices.length" x-cloak role="alert" class="text-sm text-red-600 space-y-1">
        <template x-for="(notice, i) in notices" :key="i">
            <li x-text="notice"></li>
        </template>
    </ul>

    <x-support.field-error field="files" />
</div>
