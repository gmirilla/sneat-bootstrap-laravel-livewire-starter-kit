<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimAccountRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your MySalam Account — Verification Unsuccessful',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.claim_account_rejected');
    }

    public function attachments(): array
    {
        return [];
    }
}
