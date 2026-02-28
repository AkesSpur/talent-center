<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

class CustomResetPassword extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $expireMinutes = Config::get('auth.passwords.' . Config::get('auth.defaults.passwords') . '.expire');

        return (new MailMessage)
            ->subject('Сброс пароля — Талант-центр')
            ->view('emails.reset-password', [
                'url'           => $url,
                'userName'      => $notifiable->first_name,
                'expireMinutes' => $expireMinutes,
            ]);
    }
}
