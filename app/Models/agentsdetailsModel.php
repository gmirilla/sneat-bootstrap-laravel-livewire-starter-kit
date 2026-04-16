<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class agentsdetailsModel extends Model
{
    //
    protected $fillable=[
        'uid', 'allowcredit', 'noallocated', 'noused', 'status', 'access_token', 'puid',
        'subcreditassigned', 'subcreditused', 'issubagent', 'canregistersubagent',
        // Credit pool (parent agent)
        'pool_enabled', 'pool_size', 'pool_used',
        // Per-subagent pool cap
        'pool_cap', 'pool_cap_used',
    ];


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

    /** Returns the parent agent's agentsdetailsModel row (subagent rows only). */
    public function parentAgentDetails(): ?self
    {
        if (!$this->puid) return null;
        return self::where('uid', $this->puid)->first();
    }


    

}
