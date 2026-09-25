@extends('emails.support.layout')

@section('title', 'Новое сообщение по заявке ' . $number)
@section('heading', 'Пользователь ответил')
@section('cta_url', $operatorUrl)
@section('cta_label', 'Ответить')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        По заявке <strong>{{ $number }}</strong> — «{{ $ticketSubject }}» — пользователь добавил сообщение.
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

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A; width: 150px;">Пользователь</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                {{ $authorName ?? 'Гость' }}@if($authorEmail), {{ $authorEmail }}@endif
            </td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Текущий статус</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">{{ $status->label() }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Ответственный</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">{{ $assignee ?? 'Не назначен' }}</td>
        </tr>
        @if($attachmentCount > 0)
            <tr>
                <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Файлы</td>
                <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                    {{ $attachmentCount }} — в карточке заявки, к письму не прикреплены
                </td>
            </tr>
        @endif
    </table>
@endsection

@section('footer_note', 'Это письмо отправлено автоматически. Работа с заявкой — в админ-панели.')
