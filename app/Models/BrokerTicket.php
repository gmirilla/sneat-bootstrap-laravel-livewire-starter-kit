<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrokerTicket extends Model
{
    protected $fillable = [
        'user_id', 'broker_id', 'policy_no', 'subject', 'status', 'priority',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(BrokerTicketMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(BrokerTicketMessage::class, 'ticket_id')->latestOfMany();
    }

    public function statusColour(): string
    {
        return match ($this->status) {
            'open'            => 'bg-primary',
            'in_progress'     => 'bg-info text-dark',
            'awaiting_broker' => 'bg-warning text-dark',
            'resolved'        => 'bg-success',
            'closed'          => 'bg-secondary',
            default           => 'bg-light text-dark',
        };
    }

    public function priorityColour(): string
    {
        return match ($this->priority) {
            'high'   => 'bg-warning text-dark',
            'urgent' => 'bg-danger',
            default  => 'bg-secondary',
        };
    }
}
