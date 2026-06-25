<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $claimReference
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your MySalam Account — Pending Verification',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.claim_account_created');
    }

    public function attachments(): array
    {
        return [];
    }
}
