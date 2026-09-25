@extends('emails.support.layout')

@section('title', 'Заявка ' . $number . ' просрочена')
@section('heading', 'Заявка просрочена')
@section('cta_url', $operatorUrl)
@section('cta_label', 'Открыть заявку')

@section('content')
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
        <tr>
            <td style="background-color: #FEF2F2; border-left: 3px solid #DC2626; border-radius: 0 8px 8px 0; padding: 16px 20px;">
                <p style="margin: 0; font-size: 15px; color: #991B1B; line-height: 1.6;">
                    Заявка <strong>{{ $number }}</strong> вышла за срок SLA и до сих пор не решена.
                </p>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A; width: 150px;">Тема</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">{{ $ticketSubject }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Текущий статус</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">{{ $status->label() }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Создана</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                {{ $createdAt?->timezone('Europe/Moscow')->format('d.m.Y, H:i') }} МСК
            </td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Срок ответа</td>
            <td style="padding: 4px 0; font-size: 14px; color: #DC2626; font-weight: 600;">
                {{ $deadline?->timezone('Europe/Moscow')->format('d.m.Y, H:i') }} МСК
            </td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Пользователь</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                {{ $authorName ?? 'Гость' }}@if($authorEmail), {{ $authorEmail }}@endif
            </td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Ответственный</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">{{ $assignee ?? 'Не назначен' }}</td>
        </tr>
    </table>

    <p style="margin: 0 0 20px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        Вернитесь к заявке: ответьте пользователю или обновите статус. Все файлы и комментарии — в карточке.
    </p>
@endsection

@section('note')
    Это напоминание отправляется по каждой заявке один раз — повторных писем не будет.
@endsection

@section('footer_note', 'Это письмо отправлено автоматически. Работа с заявкой — в админ-панели.')
