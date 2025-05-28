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
         $vmakeid= VehicleMake::where('name', $this->vehiclemake)->first();
         return $vmakeid ? $vmakeid->id : null;

    }
    public function getvmodelid()
    {
         $vmodelid= VehicleModel::where('name', $this->vehiclemodel)->first();
         return $vmodelid ? $vmodelid->id : null;

    }

 
}
