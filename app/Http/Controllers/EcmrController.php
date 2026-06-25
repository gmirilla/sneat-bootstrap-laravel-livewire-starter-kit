<?php

namespace App\Http\Controllers;

use App\Models\ecmr;
use App\Models\policy;
use App\Models\policyrisk;
use App\Services\EcmrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EcmrController extends Controller
{
    public function index()
    {
        $ecmrs = ecmr::all();

        return view('ecmrs.index', compact('ecmrs'));
    }

    public function validateCMR(Request $request, EcmrService $ecmrService)
    {
        $regno = $request->ecmr_regno;
        $user  = Auth::user();

        $policyrisk = policyrisk::where('regno', $regno)->orderBy('created_at', 'desc')->first();
        $policy     = $policyrisk ? policy::find($policyrisk->policyid) : null;

        $success = $ecmrService->check($regno, $policy?->id, $user?->id);

        return $success
            ? back()->with('success', 'CMR information retrieved and stored successfully.')
            : back()->with('error', 'ECMR check failed. A failure record has been saved — check the policy for details.');
    }
}
