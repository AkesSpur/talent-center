@extends('errors.layout')

@section('title', '503 — Сервис недоступен')

@section('content')
    <div class="error-icon error-icon--danger">
        <i class="fas fa-tools"></i>
    </div>
    <div class="error-code">503</div>
    <div class="error-title">Сервис временно недоступен</div>
    <div class="divider"></div>
    <p class="error-message">
        Сайт находится на техническом обслуживании и скоро будет доступен снова.
        Приносим извинения за неудобства.
    </p>
    <div class="actions">
        <a href="javascript:location.reload()" class="error-btn">
            <i class="fas fa-sync-alt"></i> Попробовать снова
        </a>
    </div>
@endsection
