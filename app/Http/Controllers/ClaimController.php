<?php

namespace App\Http\Controllers;

use App\Models\claim;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Exception;


class ClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(claim $claim)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(claim $claim)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, claim $claim)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(claim $claim)
    {
        //
    }

        /**
     * CHECK ELITE FOR CLAIM.
     */
public function claimcheck(Request $request)
{
    $claimNumber  = $request->claimnumber;

    try {

        // Query must match BOTH claim_no AND policy_no
$result = DB::connection('Elite')
    ->table('epgi_claim as e')
    ->join('epgi_policy as p', 'e.policy_id', '=', 'p.id')
    ->select(
        'p.policy_no',
        'e.claim_no',
        'e.description',
        'e.state',
        'e.loss_date',
        'e.notification_date'
    )
    ->where('e.claim_no', $claimNumber)
    ->orWhere('p.policy_no', $claimNumber)
    ->orderBy('e.loss_date', 'desc')
    ->get();

        // Not found
        if (!$result) {
            $response = [
                'status'  => 'not_found',
                'message' => 'No claim found for the provided claim and policy number',
                'data'    => []
            ];

            return view('claim.claim_check', compact('response'));
        }else{
                    // Success
        $response = [
            'status'  => 'success',
            'message' => 'Claim retrieved successfully',
            'data'    => $result
        ];

        }



    } catch (Exception $e) {

        Log::error('Claim Check Error: '.$e->getMessage());
        dd($e->getMessage());

        $response = [
            'status'  => 'error',
            'message' => 'Unable to process claim check at this time',
            'data'    => []
        ];
    }

    return view('claim.claim_check', compact('response'));
}
}
