@extends('emails.support.layout')

@section('title', 'Заявка ' . $number . ' создана')
@section('heading', 'Заявка ' . $number . ' создана')
@section('cta_label', 'Открыть заявку')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        Ваше обращение в службу поддержки принято, мы уже взяли его в работу.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A; width: 150px;">Номер заявки</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416; font-weight: 600;">{{ $number }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Категория</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                {{ $category ?? 'Без категории' }}@if($subcategory) ({{ $subcategory }})@endif
            </td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Тема</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">{{ $ticketSubject }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Дата создания</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                {{ $createdAt?->timezone('Europe/Moscow')->format('d.m.Y, H:i') }} МСК
            </td>
        </tr>
        @if($attachmentCount > 0)
            <tr>
                <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Файлы</td>
                <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                    {{ $attachmentCount }} — доступны в карточке заявки
                </td>
            </tr>
        @endif
    </table>

    <p style="margin: 0 0 20px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        Мы постараемся ответить в течение {{ $slaHours }} часов. Если появятся дополнительные материалы —
        скриншоты или файлы — прикрепите их в карточке заявки в личном кабинете.
    </p>
@endsection

@section('note')
    Не отправляйте файлы ответом на это письмо: вложения из почты в заявку не попадают.
@endsection
