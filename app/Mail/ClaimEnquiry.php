<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimEnquiry extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<string> $attachmentPaths Absolute paths to temp-stored uploads
     */
    public function __construct(
        public string  $senderName,
        public string  $senderEmail,
        public ?string $claimNo,
        public ?string $policyNo,
        public string  $messageBody,
        public array   $attachmentPaths = []
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

    public function attachments(): array
    {
        return array_map(
            fn(string $path) => Attachment::fromPath($path),
            $this->attachmentPaths
        );
    }
}
