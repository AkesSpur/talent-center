@extends('errors.layout')

@section('title', '403 — Доступ запрещён')

@section('content')
    <div class="error-icon error-icon--danger">
        <i class="fas fa-ban"></i>
    </div>
    <div class="error-code">403</div>
    <div class="error-title">Доступ запрещён</div>
    <div class="divider"></div>
    <p class="error-message">
        {{ $exception->getMessage() ?: 'У вас недостаточно прав для просмотра этой страницы. Если вы считаете, что это ошибка, обратитесь к администратору.' }}
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
