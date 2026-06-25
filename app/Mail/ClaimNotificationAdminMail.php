<?php

namespace App\Mail;

use App\Models\ClaimNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimNotificationAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User              $pendingUser,
        public ClaimNotification $notification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Pending Account — Claim Ref: ' . $this->notification->reference_no,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.claim_notification_admin');
    }

    public function attachments(): array
    {
        return [];
    }
}
