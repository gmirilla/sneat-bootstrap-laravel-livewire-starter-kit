<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lga extends Model
{
    //
            /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['lgaid', 'lganame', 'stateid']  ;

        public function getstate()
    {
        return states::where('stateid',$this->stateid)->first();
    }
}
