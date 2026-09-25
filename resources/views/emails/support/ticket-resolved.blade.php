@extends('emails.support.layout')

@section('title', 'Заявка ' . $number . ' решена')
@section('heading', 'Заявка решена')
@section('cta_label', 'Оценить ответ')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        Ваша заявка <strong>{{ $number }}</strong> — «{{ $ticketSubject }}» — переведена в статус «Решена».
    </p>

    @if($reply)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
            <tr>
                <td style="background-color: #FAF8F5; border-left: 3px solid #D4AF37; border-radius: 0 8px 8px 0; padding: 16px 20px;">
                    <p style="margin: 0; font-size: 14px; color: #2C2416; line-height: 1.6; white-space: pre-line;">{{ \Illuminate\Support\Str::limit($reply, 600) }}</p>
                </td>
            </tr>
        </table>
    @endif

    <p style="margin: 0 0 20px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        Пожалуйста, оцените качество ответа по шкале от 1 до 5 звёзд в карточке заявки.
        Если решение не помогло или вопрос остался открытым — добавьте комментарий, и мы продолжим работу.
    </p>
@endsection

@section('note')
    Если вы не ответите, заявка закроется автоматически через 3 дня. Оценку можно будет поставить и после этого.
@endsection
