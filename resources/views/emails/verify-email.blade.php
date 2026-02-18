<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF8F5; font-family: 'Inter', Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #FAF8F5; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%;">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="padding: 0 0 32px 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="width: 44px; height: 44px; background: linear-gradient(135deg, #D4AF37 0%, #C5A028 50%, #B8960F 100%); border-radius: 50%; text-align: center; vertical-align: middle;">
                                        <span style="color: #ffffff; font-size: 18px; font-weight: bold;">&#9733;</span>
                                    </td>
                                    <td style="padding-left: 12px;">
                                        <div style="font-family: 'Playfair Display', Georgia, serif; font-size: 20px; font-weight: 700; color: #8B4513; line-height: 1.2;">
                                            Талант-центр
                                        </div>
                                        <div style="font-size: 11px; color: #9A8B7A; line-height: 1.2;">
                                            Всероссийский центр талантов
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Main Card --}}
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 24px rgba(44, 36, 22, 0.08); overflow: hidden;">

                                {{-- Gold accent bar --}}
                                <tr>
                                    <td style="height: 4px; background: linear-gradient(90deg, #D4AF37 0%, #C5A028 50%, #8B4513 100%);"></td>
                                </tr>

                                {{-- Content --}}
                                <tr>
                                    <td style="padding: 40px 40px 16px 40px;">
                                        <h1 style="margin: 0 0 8px 0; font-family: 'Playfair Display', Georgia, serif; font-size: 24px; font-weight: 700; color: #2C2416;">
                                            Подтверждение электронной почты
                                        </h1>
                                        <p style="margin: 0; font-size: 15px; color: #9A8B7A; line-height: 1.6;">
                                            Здравствуйте{{ $userName ? ', ' . $userName : '' }}! Спасибо за регистрацию.
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 16px 40px 24px 40px;">
                                        <p style="margin: 0 0 24px 0; font-size: 15px; color: #2C2416; line-height: 1.6;">
                                            Для завершения регистрации и доступа ко всем возможностям платформы, пожалуйста, подтвердите ваш адрес электронной почты, нажав на кнопку ниже:
                                        </p>

                                        {{-- CTA Button --}}
                                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                                            <tr>
                                                <td align="center" style="border-radius: 10px; background: linear-gradient(135deg, #D4AF37 0%, #C5A028 50%, #B8960F 100%);">
                                                    <a href="{{ $url }}"
                                                        target="_blank"
                                                        style="display: inline-block; padding: 14px 40px; font-family: 'Inter', Arial, sans-serif; font-size: 15px; font-weight: 600; color: #2C2416; text-decoration: none; border-radius: 10px;">
                                                        Подтвердить email
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 0 40px 32px 40px;">
                                        <p style="margin: 0 0 16px 0; font-size: 13px; color: #9A8B7A; line-height: 1.6;">
                                            Если кнопка не работает, скопируйте и вставьте эту ссылку в адресную строку браузера:
                                        </p>
                                        <p style="margin: 0; font-size: 12px; color: #8B4513; word-break: break-all; line-height: 1.5;">
                                            {{ $url }}
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 0 40px 32px 40px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="border-top: 1px solid rgba(212, 175, 55, 0.2); padding-top: 20px;">
                                                    <p style="margin: 0; font-size: 13px; color: #9A8B7A; line-height: 1.6;">
                                                        Если вы не создавали аккаунт на платформе «Талант-центр», просто проигнорируйте это письмо.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding: 32px 0 0 0;">
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #9A8B7A;">
                                &copy; {{ date('Y') }} Талант-центр — Всероссийский центр талантов
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #9A8B7A;">
                                Это письмо отправлено автоматически. Пожалуйста, не отвечайте на него.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
