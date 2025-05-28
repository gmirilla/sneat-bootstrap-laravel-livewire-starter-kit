<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class policyrisk extends Model
{
    //
    protected $fillable=['policyid' ,'product_id','regno', 'engineno', 'chassisno','vehiclemake', 'vehiclemodel', 'yearofmake',
'vehiclecolor', 'contribution'];


    public function getvmakeid()
    {
         $vmakeid= vehicleMake::where('vmake', $this->vehiclemake)->first();
         return $vmakeid ? $vmakeid->niipvmid : null;

    }
    public function getvmodelid()
    {
         $vmodelid= vehicleModel::where('vmodelname', $this->vehiclemodel)->where('niipvmiid', $this->getvmakeid())
         ->first();
         return $vmodelid ? $vmodelid->vmodelid : null;

    }

 
}
