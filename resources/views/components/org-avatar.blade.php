@props(['organization', 'size' => 'md'])

@php
    $sizes = [
        'sm'  => 'w-10 h-10 text-sm',
        'md'  => 'w-14 h-14 text-base',
        'lg'  => 'w-20 h-20 text-xl',
        'xl'  => 'w-28 h-28 text-2xl',
        '2xl' => 'w-36 h-36 text-3xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($organization->avatar_url)
    <img src="{{ $organization->avatar_url }}" alt="{{ $organization->name }}"
        {{ $attributes->merge(['class' => "{$sizeClass} rounded-xl object-cover flex-shrink-0"]) }} />
@else
    <div {{ $attributes->merge(['class' => "{$sizeClass} bg-cream border border-gold/20 rounded-xl flex items-center justify-center flex-shrink-0"]) }}>
        <i class="fas fa-building text-primary" style="font-size:100%"></i>
    </div>
@endif
