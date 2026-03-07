<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Application $application) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email_notifications ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Заявка принята — ' . $this->application->contest->title)
            ->view('emails.application-submitted', [
                'userName'     => $notifiable->first_name,
                'contestTitle' => $this->application->contest->title,
                'contestUrl'   => route('contests.show', $this->application->contest),
                'categoryName' => $this->application->category?->name,
            ]);
    }
}
