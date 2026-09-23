@props(['field'])

{{-- A field's errors, whichever way the form was sent: rendered here after a
     plain post, or filled in by support-upload.js after an XHR send. --}}
<div {{ $attributes }}>
    <x-input-error :messages="[$errors->get($field), $errors->get($field . '.*')]" />
    <ul x-show="fieldErrors(@js($field)).length" x-cloak data-field-error role="alert"
        class="text-sm text-red-600 space-y-1">
        <template x-for="(message, i) in fieldErrors(@js($field))" :key="i">
            <li x-text="message"></li>
        </template>
    </ul>
</div>
