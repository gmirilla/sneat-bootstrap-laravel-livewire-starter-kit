<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrokerTicketMessage extends Model
{
    protected $fillable = [
        'ticket_id', 'user_id', 'body', 'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(BrokerTicket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(BrokerTicketAttachment::class, 'message_id');
    }
}
