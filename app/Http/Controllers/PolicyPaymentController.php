<?php

namespace App\Http\Controllers;

use App\Services\PolicyPaymentService;
use Illuminate\Http\Request;
use App\Models\policy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PolicyPaymentController extends Controller
{
    public function confirm($policyId, PolicyPaymentService $service)
    {

        abort_unless(Gate::allows('view', policy::findOrFail($policyId)), 403);

        $user=Auth::user(); 
        
        $viewModel = $service->buildConfirmationView($policyId, $user);
        

        return view('policy.confirm', $viewModel);
    }

    public function pay(Request $request, PolicyPaymentService $service)
    {
        $user=Auth::user();
        $result = $service->processPayment($request, $user);

        return redirect()
            ->route('policy.success', $result->policy_id)
            ->with('success','Policy issued successfully');
    }
}