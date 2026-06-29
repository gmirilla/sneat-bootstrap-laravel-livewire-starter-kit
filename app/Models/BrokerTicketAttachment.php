<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrokerTicketAttachment extends Model
{
    protected $fillable = [
        'message_id', 'original_name', 'path', 'mime_type', 'size',
    ];

    public function message()
    {
        return $this->belongsTo(BrokerTicketMessage::class, 'message_id');
    }
}
