<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class agentsdetailsModel extends Model
{
    //
    protected $fillable=['uid', 'allowcredit','noallocated', 'noused', 'status','access_token', 'puid', 
    'subcreditassigned', 'subcreditused', 'issubagent', 'canregistersubagent'];


    public function getuserinfo()
    {
        return User::where('id',$this->uid)->first();
    }
    public function getagg()
    {
       
        return policy::where('agent_id',$this->uid)->get();
    }

        //Get all Parent  Agent  and their Details
    public function getparentdetails() 
    { 
        return User::where('id', $this->puid)->first();
    }


    

}
