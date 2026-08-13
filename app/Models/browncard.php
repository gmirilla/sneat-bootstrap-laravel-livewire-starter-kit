<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class browncard extends Model
{
    protected $fillable = [
        'policyid',
        'stringid',
        'policynumber',
        'regno',
        'browncardnumber',
        'elitesuccess',
        'elitemsg',
    ];

    public function policy()
    {
        return $this->belongsTo(policy::class, 'policyid');
    }
}
