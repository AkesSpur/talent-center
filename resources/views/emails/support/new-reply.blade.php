@extends('emails.support.layout')

@section('title', 'Новый ответ по заявке ' . $number)
@section('heading', 'Поддержка ответила')
@section('cta_label', 'Прочитать ответ')

@section('content')
    <p style="margin: 0 0 16px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        По заявке <strong>{{ $number }}</strong> — «{{ $ticketSubject }}» — появился новый ответ от службы поддержки.
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

    @if($attachmentCount > 0)
        <p style="margin: 0 0 16px 0; font-size: 14px; color: #9A8B7A; line-height: 1.6;">
            К ответу приложено файлов: {{ $attachmentCount }}. Они доступны в карточке заявки — к письму файлы не прикрепляются.
        </p>
    @endif

    <p style="margin: 0 0 20px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
        Чтобы продолжить переписку, откройте заявку в личном кабинете.
    </p>
@endsection

@section('note')
    Текущий статус заявки — «{{ $status->label() }}».
@endsection
