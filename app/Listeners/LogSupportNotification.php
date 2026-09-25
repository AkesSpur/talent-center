<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\SupportNotificationLog;
use App\Notifications\Support\SupportNotification;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Writes the notification history required by ТЗ 11.2.
 *
 * Registration is automatic: bootstrap/app.php calls withEvents(), which discovers
 * any public handle* method in app/Listeners. Do NOT also register this in a service
 * provider — that would write every row twice.
 */
final class LogSupportNotification
{
    public function handleNotificationSent(NotificationSent $event): void
    {
        $this->write(
            $event->notifiable,
            $event->notification,
            $event->channel,
            SupportNotificationLog::STATUS_SENT,
        );
    }

    public function handleNotificationFailed(NotificationFailed $event): void
    {
        $exception = $event->data['exception'] ?? null;

        $this->write(
            $event->notifiable,
            $event->notification,
            $event->channel,
            SupportNotificationLog::STATUS_FAILED,
            $exception instanceof \Throwable ? Str::limit($exception->getMessage(), 2000, '') : null,
        );
    }

    private function write(
        object $notifiable,
        object $notification,
        string $channel,
        string $status,
        ?string $error = null,
    ): void {
        if ($channel !== 'mail' || ! $notification instanceof SupportNotification) {
            return; // contest and auth mail are none of our business
        }

        $recipient = $this->recipient($notifiable, $notification);

        if ($recipient === null) {
            return; // recipient_email is NOT NULL
        }

        try {
            SupportNotificationLog::create([
                'ticket_id'       => $notification->ticket->getKey(),
                'recipient_email' => $recipient,
                'template_type'   => $notification->template()->value,
                'status'          => $status,
                'error'           => $error,
            ]);
        } catch (\Throwable $e) {
            // NotificationSent fires inside the queued job, after the mail has gone out.
            // Throwing here would fail the job, the worker would retry it, and the
            // recipient would get the same letter two or three times. A lost log row
            // is the cheaper failure.
            Log::warning('Не удалось записать историю уведомления: ' . $e->getMessage());
        }
    }

    /** Works for a User and for an on-demand (guest) notifiable alike. */
    private function recipient(object $notifiable, object $notification): ?string
    {
        $route = method_exists($notifiable, 'routeNotificationFor')
            ? $notifiable->routeNotificationFor('mail', $notification)
            : null;

        if (is_array($route)) {
            $route = array_key_first($route) ?: null; // ['address' => 'Name']
        }

        return is_string($route) && $route !== '' ? $route : null;
    }
}
