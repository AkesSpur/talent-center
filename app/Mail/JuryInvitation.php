<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JuryInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Organization $organization,
        public readonly User $invitedBy,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Приглашение в жюри — ' . $this->organization->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.jury-invitation',
            with: [
                'organizationName' => $this->organization->name,
                'invitedByName'    => $this->invitedBy->full_name,
                'registerUrl'      => route('register'),
            ],
        );
    }
}
