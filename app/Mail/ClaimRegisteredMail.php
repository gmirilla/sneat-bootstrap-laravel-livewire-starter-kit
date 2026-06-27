<?php

namespace App\Mail;

use App\Models\ClaimNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ClaimNotification $notification) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Claim Has Been Registered — ' . $this->notification->elite_claim_no,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.claim_registered');
    }

    public function attachments(): array
    {
        return [];
    }
}
