@if($attachments->count())
    @php [$images, $files] = $attachments->partition(fn ($file) => $file->isPreviewable()); @endphp

    {{-- Photos and screenshots: a thumbnail that opens <x-support.lightbox>; without JS it opens in a new tab --}}
    @if($images->isNotEmpty())
        <ul class="mt-4 flex flex-wrap gap-2" aria-label="Изображения">
            @foreach($images as $file)
                <li>
                    <a href="{{ $file->previewUrl() }}" target="_blank" rel="noopener"
                        data-lightbox
                        data-src="{{ $file->previewUrl() }}"
                        data-name="{{ $file->original_name }}"
                        data-size="{{ $file->humanSize() }}"
                        data-download="{{ $file->downloadUrl() }}"
                        @click.prevent="$dispatch('support-lightbox', $el)"
                        title="{{ $file->original_name }} · {{ $file->humanSize() }}"
                        aria-label="Открыть изображение {{ $file->original_name }}, {{ $file->humanSize() }}"
                        class="group relative block w-28 h-28 sm:w-32 sm:h-32 rounded-lg overflow-hidden border border-gold/20 bg-cream hover:border-primary/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 transition-colors">
                        <img src="{{ $file->thumbnailUrl() }}" alt="" loading="lazy" decoding="async"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                        <span class="absolute inset-x-0 bottom-0 px-2 pt-4 pb-1 bg-gradient-to-t from-dark/70 to-transparent text-[11px] text-white text-left truncate">
                            {{ $file->humanSize() }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    @if($files->isNotEmpty())
        <ul class="mt-3 flex flex-wrap gap-2">
            @foreach($files as $file)
                <li>
                    <a href="{{ $file->downloadUrl() }}" title="Скачать {{ $file->original_name }} · {{ $file->humanSize() }}"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-cream border border-gold/10 rounded-lg text-xs text-dark hover:border-primary/30 transition-colors">
                        <i class="fas {{ $file->iconClass() }} text-primary/60" aria-hidden="true"></i>
                        <span class="max-w-[220px] truncate">{{ $file->original_name }}</span>
                        <span class="text-warm-gray">{{ $file->humanSize() }}</span>
                        <i class="fas fa-download text-warm-gray/60" aria-hidden="true"></i>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endif
