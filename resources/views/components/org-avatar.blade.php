@props(['organization', 'size' => 'md'])

@php
    $sizes = [
        'xs'  => 'w-8 h-8 text-sm rounded-full',
        'sm'  => 'w-10 h-10 text-sm rounded-xl',
        'md'  => 'w-14 h-14 text-base rounded-xl',
        'lg'  => 'w-20 h-20 text-xl rounded-xl',
        'xl'  => 'w-28 h-28 text-2xl rounded-xl',
        '2xl' => 'w-36 h-36 text-3xl rounded-xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($organization->avatar_url)
    <img src="{{ $organization->avatar_url }}" alt="{{ $organization->name }}"
        {{ $attributes->merge(['class' => "{$sizeClass} object-cover flex-shrink-0"]) }} />
@else
    <div {{ $attributes->merge(['class' => "{$sizeClass} bg-cream border border-gold/20 flex items-center justify-center flex-shrink-0"]) }}>
        <i class="fas fa-building text-primary" style="font-size:100%"></i>
    </div>
@endif
