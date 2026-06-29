<?php

namespace App\Notifications;

use App\Models\BrokerTicket;
use App\Models\BrokerTicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrokerTicketNewActivity extends Notification
{
    use Queueable;

    public function __construct(
        public readonly BrokerTicket        $ticket,
        public readonly BrokerTicketMessage $message,
        public readonly string              $activityType  // 'new_ticket' | 'broker_reply'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url     = route('admin.broker-tickets.show', $this->ticket);
        $broker  = $this->ticket->user;
        $subject = $this->activityType === 'new_ticket'
            ? "New Broker Ticket: [{$this->ticket->policy_no}] {$this->ticket->subject}"
            : "Broker Reply: [{$this->ticket->policy_no}] {$this->ticket->subject}";

        $intro = $this->activityType === 'new_ticket'
            ? "A new support ticket has been opened by broker **{$broker->name}** (ID #{$this->ticket->broker_id})."
            : "Broker **{$broker->name}** has replied to ticket #{$this->ticket->id}.";

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello Technical Team,')
            ->line($intro)
            ->line("**Policy:** {$this->ticket->policy_no}")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Priority:** " . ucfirst($this->ticket->priority))
            ->line('> ' . \Str::limit(strip_tags($this->message->body), 200))
            ->action('View Ticket', $url)
            ->salutation('MySalam Online — Automated Notification');
    }
}
