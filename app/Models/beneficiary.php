<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class beneficiary extends Model
{
    //
    protected $fillable = [
        'policyid',
        'name',
        'relationship',
        'percentage',
        'telno',
        'address'
    ];
}
