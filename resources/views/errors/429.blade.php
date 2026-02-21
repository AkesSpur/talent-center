@extends('errors.layout')

@section('title', '429 — Слишком много запросов')

@section('content')
    <div class="error-icon error-icon--warning">
        <i class="fas fa-hand-paper"></i>
    </div>
    <div class="error-code">429</div>
    <div class="error-title">Слишком много запросов</div>
    <div class="divider"></div>
    <p class="error-message">
        Вы отправили слишком много запросов за короткое время.
        Пожалуйста, подождите немного и попробуйте снова.
    </p>
    <div class="actions">
        <a href="javascript:history.back()" class="error-btn">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
        <a href="/" class="error-btn error-btn--secondary">
            <i class="fas fa-home"></i> На главную
        </a>
    </div>
@endsection
