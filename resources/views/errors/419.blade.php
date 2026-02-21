@extends('errors.layout')

@section('title', '419 — Сессия истекла')

@section('content')
    <div class="error-icon error-icon--warning">
        <i class="fas fa-clock"></i>
    </div>
    <div class="error-code">419</div>
    <div class="error-title">Сессия истекла</div>
    <div class="divider"></div>
    <p class="error-message">
        Время вашей сессии истекло. Это может произойти, если страница была
        открыта слишком долго. Пожалуйста, обновите страницу и попробуйте снова.
    </p>
    <div class="actions">
        <a href="javascript:location.reload()" class="error-btn">
            <i class="fas fa-sync-alt"></i> Обновить страницу
        </a>
        <a href="/" class="error-btn error-btn--secondary">
            <i class="fas fa-home"></i> На главную
        </a>
    </div>
@endsection
