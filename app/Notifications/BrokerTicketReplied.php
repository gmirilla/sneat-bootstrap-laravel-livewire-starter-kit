<?php

namespace App\Notifications;

use App\Models\BrokerTicket;
use App\Models\BrokerTicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrokerTicketReplied extends Notification
{
    use Queueable;

    public function __construct(
        public readonly BrokerTicket        $ticket,
        public readonly BrokerTicketMessage $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'      => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'policy_no'      => $this->ticket->policy_no,
            'sender'         => $this->message->user->name,
            'preview'        => \Str::limit(strip_tags($this->message->body), 80),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('broker.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Re: [{$this->ticket->policy_no}] {$this->ticket->subject}")
            ->greeting("Hello {$notifiable->firstname},")
            ->line("The technical department has replied to your ticket regarding policy **{$this->ticket->policy_no}**.")
            ->line("**{$this->ticket->subject}**")
            ->line('> ' . \Str::limit(strip_tags($this->message->body), 120))
            ->action('View Ticket', $url)
            ->line('Log in to view the full message and reply.');
    }
}
