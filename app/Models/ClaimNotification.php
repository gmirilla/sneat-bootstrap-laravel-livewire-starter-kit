<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimNotification extends Model
{
    protected $fillable = [
        'reference_no',
        'policy_no',
        'policy_type',
        'policy_start',
        'policy_end',
        'policy_source',
        'claimant_name',
        'claimant_email',
        'claimant_phone',
        'incident_date',
        'description',
        'user_id',
        'status',
        'elite_claim_no',
    ];

    protected $casts = [
        'policy_start'  => 'date',
        'policy_end'    => 'date',
        'incident_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claimAttachments()
    {
        return $this->hasMany(ClaimAttachment::class);
    }
}
