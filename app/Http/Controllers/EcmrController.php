<?php

namespace App\Http\Controllers;

use App\Models\ecmr;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\policy;
use App\Models\policyrisk;
use Illuminate\Support\Facades\Auth;

class EcmrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $ecmrs = ecmr::all();

        return view('ecmrs.index', compact('ecmrs'));
    }


    public function validateCMR(Request $request)
    {
        // TO DO VALIDATION
    $ecmr_check=$request->ecmr_regno;

    //Get policy details 
    $policyrisk=policyrisk::where('regno', $ecmr_check)->orderBy('created_at', 'desc')->first();
    $policy = $policyrisk ? Policy::find($policyrisk->policyid) : null;
    $user=Auth::user();
    
        //Use GET TOKEN to LOGIN
        $response = Http::post(env('eMCR_URL') . 'api/apiuser/login', [
            'username' => env('eMCR_USERNAME'),
            'password' => env('eMCR_PASSWORD'),
        ]);

        $jsonObject = json_decode($response->body());

        if ($jsonObject->statusCode == 0) {


            $querysearch = Http::withToken($jsonObject->data->token)->get(env('eMCR_URL') . 'api/insurance/cmrisinfo/v1/license/' .$ecmr_check);
            $queryresponse = json_decode($querysearch->body());

            
            //store the search result in database
            $ecmr = new ecmr();
            $ecmr->licence_plate= $ecmr_check;
            $ecmr->response = $querysearch->body();
            $ecmr->status = $queryresponse->data->cmr_status ?? 'Unknown';
            $ecmr->cmr_number = $queryresponse->data->cmr_number ?? 'N/A';
            $ecmr->message = $queryresponse->message;
            $ecmr->policy_id = $policy ? $policy->id : null;   
            $ecmr->cuid= $user ? $user->id : null;  
            $ecmr->save();
            
            return back()->with('success', 'CMR information retrieved and stored successfully.');
        } else {
            return back()->with('error', 'Login failed. Message: ' . $jsonObject->message);
        }
        

        return view('ecmrs.index', compact('ecmrs'));
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
    public function show(ecmr $ecmr)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ecmr $ecmr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ecmr $ecmr)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ecmr $ecmr)
    {
        //
    }
}
