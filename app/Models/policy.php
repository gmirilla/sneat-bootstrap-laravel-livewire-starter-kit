<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class policy extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'policyno',
        'insured_id',
        'insured_name',
        'agent_id',
        'status',
        'naicom_uid',
        'naicom_status',
        'niid_status',
        'niip_status',
        'elite_msg',
        'commission',
        'contribution',
        'start_date',
        'end_date',
        'create_uid',
        'update_uid',
        'producttype',
        'usekey',
        'vehicleuse',
        'insurancetype',
        'niidresponse',
        'niipvehicleuse',
        'lgaid',
        'stateid',
        'address',
        'policytype',
        'frequency',
        'paymenttype',
        'cancelled',
        'cancellation_reason',
        'cancellation_date',
        'cancellation_uid'
    ];

    public function risk()
    {
        return $this->hasOne(policyrisk::class, 'policyid');
    }

    public function agentUser()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function insuredUser()
    {
        return $this->belongsTo(User::class, 'insured_id');
    }

    public function getrisk()
    {
        return $this->relationLoaded('risk')
            ? $this->risk
            : policyrisk::where('policyid', $this->id)->first();
    }

    public function getagentname()
    {
        $agent = $this->relationLoaded('agentUser')
            ? $this->agentUser
            : User::where('id', $this->agent_id)->first();
        return $agent ? $agent->name : 'Agent Not Found';
    }

    public function getparentagentname()
    {
        $agent = agentsdetailsModel::where('uid', $this->agent_id)->first();
        if ($agent && $agent->puid) {
            $parentAgent = User::where('id', $agent->puid)->first();
            return $parentAgent ? $parentAgent->name : 'Parent Agent Not Found';
        }
        $parentAgent = User::where('id', $this->agent_id)->first();
        return $parentAgent ? $parentAgent->name : 'Parent Agent Not Found' ;
    }

    public function getaddress()
    {
        $address = User::where('id', $this->insured_id)->first();
        return $address ? $address->address : 'Address Not Found';
    }
    public function getuser()
    {

        return User::where('id', $this->insured_id)->first();
    }

    public function getlga()
    {
        $lga = lga::where('lgaid', $this->lgaid)->first();
        return $lga ? $lga->name : 'LGA Not Found';
    }

    public function getNiipStatus()
    {
        $data = json_decode($this->niip_status, true) ?? false;
        if (!isset($data['status'])) {

            # code...
        } else {
            $data['isSuccess'] = false;
            $data['message'] = $data['title'];
        }

        //isSuccess

        return $data;
    }

    public function getpayments()
    {
        return paystacktransaction::where('policy_id', $this->id)->get();
    }

    public function getsuccesspayments()
    {
        return paystacktransaction::where('policy_id', $this->id)->where('status', 'Payment Sucessful')->get();
    }


    public function getbeneficiaries()
    {
        return beneficiary::where('policy_id', $this->id)->get();
    }
    public function getemcr()
    {
        $regno = optional($this->getrisk())->regno;

        return $regno
            ? ecmr::where('licence_plate', $regno)->latest()->first()
            : null;
    }
}
