<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimEnquiry extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string  $senderName,
        public string  $senderEmail,
        public ?string $claimNo,
        public ?string $policyNo,
        public string  $messageBody
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->senderEmail],
            subject: 'Claim Enquiry' . ($this->claimNo ? ' — Claim #' . $this->claimNo : ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.claim_enquiry',
        );
    }
}
