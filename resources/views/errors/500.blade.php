@extends('errors.layout')

@section('title', '500 — Ошибка сервера')

@section('content')
    <div class="error-icon error-icon--danger">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="error-code">500</div>
    <div class="error-title">Внутренняя ошибка сервера</div>
    <div class="divider"></div>
    <p class="error-message">
        Произошла непредвиденная ошибка на сервере. Мы уже работаем над её устранением.
        Пожалуйста, попробуйте позже или обратитесь в поддержку.
    </p>
    <div class="actions">
        <a href="/" class="error-btn">
            <i class="fas fa-home"></i> На главную
        </a>
        <a href="javascript:location.reload()" class="error-btn error-btn--secondary">
            <i class="fas fa-sync-alt"></i> Обновить
        </a>
    </div>
@endsection
