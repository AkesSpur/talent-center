<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationEvaluated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Application $application) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email_notifications ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isRejected = $this->application->status === ApplicationStatus::Rejected;

        $diplomaUrl = null;
        if (! $isRejected && $this->application->diploma) {
            $diplomaUrl = route('diplomas.download', $this->application->diploma);
        }

        if ($isRejected) {
            $subject  = 'Заявка отклонена — ' . $this->application->contest->title;
            $template = 'emails.application-rejected';
        } else {
            $subject  = 'Результаты оценки — ' . $this->application->contest->title;
            $template = 'emails.application-placed';
        }

        return (new MailMessage)
            ->subject($subject)
            ->view($template, [
                'userName'        => $notifiable->first_name,
                'contestTitle'    => $this->application->contest->title,
                'statusLabel'     => $this->application->status->label(),
                'rejectionReason' => $this->application->rejection_reason,
                'diplomaUrl'      => $diplomaUrl,
                'contestUrl'      => route('contests.show', $this->application->contest),
            ]);
    }
}
