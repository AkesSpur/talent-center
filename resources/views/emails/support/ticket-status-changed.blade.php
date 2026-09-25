@extends('emails.support.layout')

@section('title', 'Статус заявки ' . $number . ' изменён')
@section('heading', 'Статус заявки изменён')
@section('cta_label', 'Открыть заявку')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        Статус вашей заявки <strong>{{ $number }}</strong> — «{{ $ticketSubject }}» — изменился.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A; width: 150px;">Было</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">{{ $previous->label() }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Стало</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416; font-weight: 600;">{{ $status->label() }}</td>
        </tr>
    </table>

    @if($reply)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
            <tr>
                <td style="background-color: #FAF8F5; border-left: 3px solid #D4AF37; border-radius: 0 8px 8px 0; padding: 16px 20px;">
                    <p style="margin: 0; font-size: 14px; color: #2C2416; line-height: 1.6; white-space: pre-line;">{{ \Illuminate\Support\Str::limit($reply, 600) }}</p>
                </td>
            </tr>
        </table>
    @endif

    @if($needsClarification)
        <p style="margin: 0 0 20px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
            Нам нужны уточнения с вашей стороны. Откройте заявку, добавьте комментарий или приложите
            недостающие материалы — после этого мы продолжим работу.
        </p>
    @else
        <p style="margin: 0 0 20px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
            Посмотреть переписку и ответить можно в карточке заявки в личном кабинете.
        </p>
    @endif
@endsection

@section('note')
    Срок ответа по заявке — {{ $slaHours }} часов с момента её создания.
@endsection
