<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContestFinalized extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Application $application) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email_notifications ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $diplomaUrl = null;
        if ($this->application->diploma) {
            $diplomaUrl = route('diplomas.download', $this->application->diploma);
        }

        return (new MailMessage)
            ->subject('Результаты опубликованы — ' . $this->application->contest->title)
            ->view('emails.contest-finalized', [
                'userName'     => $notifiable->first_name,
                'contestTitle' => $this->application->contest->title,
                'statusLabel'  => $this->application->status->label(),
                'diplomaUrl'   => $diplomaUrl,
                'contestUrl'   => route('contests.show', $this->application->contest),
            ]);
    }
}
