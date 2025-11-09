<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class paystacktransaction extends Model
{
    //
    protected $fillable = [
        'ref_id',
        'policy_id',
        'policyno',
        'email',
        'amount',
        'access_code',
        'reference_code',
        'status',
    ];

}
