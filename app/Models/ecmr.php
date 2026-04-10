<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ecmr extends Model
{
    //
    protected $fillable = [
        'policy_id',
        'cuid',
        'licence_plate',
        'response',
        'status',
        'message',
        'cmr_number'
    ];

    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'cuid');
    }
}
