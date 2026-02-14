<?php

namespace App\Services;

use App\Models\policy;
use App\Models\agentsdetailsModel;
use App\Services\Payments\PaystackService;
use Illuminate\Validation\ValidationException;

class PolicyPaymentService
{
    public function buildConfirmationView($policyId, $user): array
    {

 
        $policy = policy::where('id', $policyId)->first();
        //$policy = Policy::with('risk')->find($policyId);

        $agent = agentsdetailsModel::where('uid',$user->id)->first();

        $creditLeft = 0;
        $allowCredit = false;

        if($agent){
            $creditLeft = $user->role === 'subagent'
                ? $agent->subcreditassigned - $agent->subcreditused
                : $agent->noallocated - $agent->noused;

            $allowCredit = $agent->allowcredit && $creditLeft > 0;
        }

        return [
            'policy' => $policy,
            'risk' => $policy->risk,
            'paymentMethods' => $this->availablePaymentMethods($allowCredit),
            'creditLeft' => $creditLeft
        ];
    }

    public function processPayment($request, $user)
    {
        if($request->filled('paystack_reference')){
            return $this->payWithPaystack($request->paystack_reference, $user);
        }

        if($request->filled('agency_credit')){
            return $this->payWithCredit($request->policy_id, $user);
        }

        throw ValidationException::withMessages([
            'payment' => 'Invalid payment method'
        ]);
    }

    private function payWithPaystack($reference, $user)
    {
        $verification = app(PaystackService::class)->verify($reference);

        if(!$verification->status){
            throw ValidationException::withMessages([
                'payment' => 'Payment verification failed'
            ]);
        }

        return $this->activatePolicy($verification->metadata->policy_id, $user);
    }

    private function payWithCredit($policyId, $user)
    {
        // deduct credit
        return $this->activatePolicy($policyId, $user);
    }

    private function activatePolicy($policyId, $user)
    {
        $policy = Policy::findOrFail($policyId);

        $policy->activate($user);

        return (object)['policy_id' => $policy->id];
    }

    private function availablePaymentMethods($allowCredit)
    {
        return [
            'paystack' => true,
            'credit' => $allowCredit
        ];
    }
}
