@extends('emails.support.layout')

@section('title', 'Новая заявка ' . $number)
@section('heading', 'Новая заявка ' . $number)
@section('cta_url', $operatorUrl)
@section('cta_label', 'Взять в работу')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        В систему поступило новое обращение.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
        <tr>
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A; width: 150px;">Пользователь</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416;">
                {{ $authorName ?? 'Гость' }}@if($authorEmail), {{ $authorEmail }}@endif
            </td>
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
            <td style="padding: 4px 0; font-size: 14px; color: #9A8B7A;">Ответить до</td>
            <td style="padding: 4px 0; font-size: 14px; color: #2C2416; font-weight: 600;">
                {{ $deadline?->timezone('Europe/Moscow')->format('d.m.Y, H:i') }} МСК
            </td>
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

    @if($description)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 20px 0;">
            <tr>
                <td style="background-color: #FAF8F5; border-left: 3px solid #D4AF37; border-radius: 0 8px 8px 0; padding: 16px 20px;">
                    <p style="margin: 0; font-size: 14px; color: #2C2416; line-height: 1.6; white-space: pre-line;">{{ \Illuminate\Support\Str::limit($description, 600) }}</p>
                </td>
            </tr>
        </table>
    @endif
@endsection

@section('note')
    Возьмите заявку в работу и смените статус на «В работе» при первом ответе. Срок по SLA — {{ $slaHours }} часов с момента создания.
@endsection

@section('footer_note', 'Это письмо отправлено автоматически. Работа с заявкой — в админ-панели.')
