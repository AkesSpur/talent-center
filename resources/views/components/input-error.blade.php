@props(['messages'])

@php
    // $errors->get('files.*') returns one list per file — flatten so every message prints as text.
    $messages = array_values(array_unique(\Illuminate\Support\Arr::flatten((array) $messages)));
@endphp

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ($messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
