@extends('errors.layout')

@section('title', '404 — Страница не найдена')

@section('content')
    <div class="error-icon error-icon--warning">
        <i class="fas fa-search"></i>
    </div>
    <div class="error-code">404</div>
    <div class="error-title">Страница не найдена</div>
    <div class="divider"></div>
    <p class="error-message">
        Запрашиваемая страница не существует или была перемещена.
        Проверьте правильность адреса или вернитесь на главную страницу.
    </p>
    <div class="actions">
        <a href="/" class="error-btn">
            <i class="fas fa-home"></i> На главную
        </a>
        <a href="javascript:history.back()" class="error-btn error-btn--secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>
@endsection
