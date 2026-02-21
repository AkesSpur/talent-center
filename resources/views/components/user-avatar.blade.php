@props(['user', 'size' => 'md'])

@php
    $sizes = [
        'xs' => 'w-6 h-6 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-14 h-14 text-lg',
        'xl' => 'w-20 h-20 text-2xl',
        '2xl' => 'w-32 h-32 text-4xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($user->avatar_url)
    <img src="{{ $user->avatar_url }}" alt="{{ $user->initials }}"
        {{ $attributes->merge(['class' => "{$sizeClass} rounded-full object-cover flex-shrink-0"]) }} />
@else
    <div {{ $attributes->merge(['class' => "{$sizeClass} gradient-gold rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0"]) }}>
        {{ $user->initials }}
    </div>
@endif
