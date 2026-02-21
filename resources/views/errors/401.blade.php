@extends('errors.layout')

@section('title', '401 — Не авторизован')

@section('content')
    <div class="error-icon error-icon--warning">
        <i class="fas fa-lock"></i>
    </div>
    <div class="error-code">401</div>
    <div class="error-title">Требуется авторизация</div>
    <div class="divider"></div>
    <p class="error-message">
        Для доступа к этой странице необходимо войти в свою учётную запись.
        Пожалуйста, авторизуйтесь и попробуйте снова.
    </p>
    <div class="actions">
        <a href="{{ route('login') }}" class="error-btn">
            <i class="fas fa-sign-in-alt"></i> Войти
        </a>
        <a href="/" class="error-btn error-btn--secondary">
            <i class="fas fa-home"></i> На главную
        </a>
    </div>
@endsection
