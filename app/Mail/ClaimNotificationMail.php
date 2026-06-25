<?php

namespace App\Mail;

use App\Models\ClaimNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClaimNotification $notification,
        public bool $accountCreated,
        public bool $accountPending
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->notification->claimant_email],
            subject: 'Claim Notification — Ref: ' . $this->notification->reference_no
                   . ' [Policy: ' . $this->notification->policy_no . ']',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.claim_notification');
    }

    public function attachments(): array
    {
        return [];
    }
}
