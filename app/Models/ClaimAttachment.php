<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimAttachment extends Model
{
    protected $fillable = ['claim_notification_id', 'original_name', 'path', 'mime_type', 'size'];

    public function claimNotification()
    {
        return $this->belongsTo(ClaimNotification::class);
    }
}
