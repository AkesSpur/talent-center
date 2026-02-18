<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;


class CustomVerifyEmail extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Подтверждение электронной почты — Талант-центр')
            ->view('emails.verify-email', [
                'url' => $verificationUrl,
                'userName' => $notifiable->first_name,
            ]);
    }
}
