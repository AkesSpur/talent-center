@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-warm-gray/40 focus:border-primary focus:ring-primary rounded-lg shadow-sm']) }}>
