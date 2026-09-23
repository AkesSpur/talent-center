@props(['action'])

{{--
    A helpdesk form that can carry attachments (resources/js/support-upload.js).
    Without JS it is an ordinary multipart post; with JS it is sent over XHR
    with a progress bar, and the typed text survives any refusal.
    Extra Alpine state belongs on a wrapping element — this one owns x-data.
--}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data"
    x-data="supportUpload(@js(\App\Services\SupportAttachmentService::pickerConfig()))"
    data-upload-drop
    @submit="submit($event)"
    @dragenter="dragEnter($event)"
    @dragleave="dragLeave($event)"
    @dragover.prevent=""
    @drop.prevent="dropped($event)"
    {{ $attributes }}>
    @csrf
    {{ $slot }}
</form>
