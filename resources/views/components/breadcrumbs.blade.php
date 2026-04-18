@props(['items'])
<nav class="text-sm text-warm-gray mb-1">
    @foreach($items as $item)
        @if(!$loop->last)
            <a href="{{ $item['url'] }}" class="hover:text-primary transition-colors">{{ $item['label'] }}</a>
            <span class="mx-1 opacity-50">›</span>
        @else
            <span class="text-dark">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
