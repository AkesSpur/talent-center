{{-- Laravel falls back to this page for any 4xx code without its own view, e.g. 405 when /logout is opened by link --}}
@extends('errors.layout')

@php
    $code = $exception->getStatusCode();
    [$title, $message] = match ($code) {
        400 => ['Неверный запрос', 'Сервер не смог разобрать запрос. Обновите страницу и попробуйте ещё раз.'],
        405 => ['Действие недоступно по ссылке', 'Этот адрес выполняет действие — например, выход из аккаунта или отправку формы — и срабатывает только по кнопке на сайте. Вернитесь назад или перейдите на главную.'],
        default => ['Запрос не выполнен', 'Не получилось открыть страницу. Вернитесь назад или перейдите на главную.'],
    };
@endphp

@section('title', $code . ' — ' . $title)

@section('content')
    <div class="error-icon error-icon--warning">
        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
    </div>
    <div class="error-code">{{ $code }}</div>
    <div class="error-title">{{ $title }}</div>
    <div class="divider"></div>
    <p class="error-message">{{ $message }}</p>
    <div class="actions">
        <a href="javascript:history.back()" class="error-btn">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Назад
        </a>
        <a href="/" class="error-btn error-btn--secondary">
            <i class="fas fa-home" aria-hidden="true"></i> На главную
        </a>
    </div>
@endsection
