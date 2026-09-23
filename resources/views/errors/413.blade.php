@extends('errors.layout')

@php
    $perFile = \App\Services\SupportAttachmentService::formatBytes(\App\Services\SupportAttachmentService::maxFileBytes());
    $perMessage = \App\Services\SupportAttachmentService::formatBytes(\App\Services\SupportAttachmentService::maxTotalBytes());
@endphp

@section('title', '413 — Слишком большие файлы')

@section('content')
    <div class="error-icon error-icon--warning">
        <i class="fas fa-file-circle-exclamation" aria-hidden="true"></i>
    </div>
    <div class="error-code">413</div>
    <div class="error-title">Файлы слишком большие</div>
    <div class="divider"></div>
    <p class="error-message">
        Сервер не принял отправку: вложения оказались слишком большими.
        Можно прикрепить файлы до {{ $perFile }} каждый и всего до {{ $perMessage }} в одном сообщении.
        Вернитесь к форме — обычно браузер сохраняет введённый текст, — уберите часть файлов или выберите файлы поменьше.
    </p>
    <div class="actions">
        <a href="javascript:history.back()" class="error-btn">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Вернуться к форме
        </a>
        <a href="/" class="error-btn error-btn--secondary">
            <i class="fas fa-home" aria-hidden="true"></i> На главную
        </a>
    </div>
@endsection
