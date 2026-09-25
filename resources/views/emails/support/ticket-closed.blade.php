@extends('emails.support.layout')

@section('title', 'Заявка ' . $number . ' закрыта')
@section('heading', 'Заявка закрыта')
@section('cta_label', 'История обращений')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        Ваша заявка <strong>{{ $number }}</strong> — «{{ $ticketSubject }}» — закрыта.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
        @if($reason)
            <tr>
                <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A; width: 150px;">Причина закрытия</td>
                <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">{{ $reason->label() }}</td>
            </tr>
        @endif
        @if($closedAt)
            <tr>
                <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Дата закрытия</td>
                <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                    {{ $closedAt->timezone('Europe/Moscow')->format('d.m.Y, H:i') }} МСК
                </td>
            </tr>
        @endif
    </table>

    @if($canRate)
        <p style="margin: 0 0 20px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
            Вы всё ещё можете оценить работу поддержки по этой заявке — откройте её в личном кабинете
            и поставьте оценку от 1 до 5 звёзд.
        </p>
    @else
        <p style="margin: 0 0 20px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
            Спасибо за обращение и за оценку.
        </p>
    @endif
@endsection

@section('note')
    Если вопрос появится снова, создайте новую заявку в разделе «Поддержка» — в закрытую написать уже нельзя.
@endsection
