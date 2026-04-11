<?php

namespace App\Http\Controllers;

use App\Models\ecmr;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Http\Request;
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
        $ecmr_check = $request->ecmr_regno;

        //Get policy details 
        $policyrisk = policyrisk::where('regno', $ecmr_check)->orderBy('created_at', 'desc')->first();
        $policy = $policyrisk ? Policy::find($policyrisk->policyid) : null;
        $user = Auth::user();

        $secret   = env('PROXY_SECRET');
        $proxyUrl = rtrim(env('PROXY_URL'), '/');

        // Use system curl binary — bypasses PHP's cURL extension and its stale CA bundle
        $loginBody = $this->curlExec('POST', $proxyUrl . '/api/ecmr/login', $secret);
        if ($loginBody === null) {
            return back()->with('error', 'ECMR login request failed: system curl error.');
        }
        $jsonObject = json_decode($loginBody);

        if (!isset($jsonObject->statusCode)) {
            return back()->with('error', 'ECMR API returned an unexpected response during login.');
        }

        if ($jsonObject->statusCode == 0) {
            $lookupUrl   = $proxyUrl . '/api/ecmr/lookup?token=' . urlencode($jsonObject->data->token) . '&regno=' . urlencode($ecmr_check);
            $lookupBody  = $this->curlExec('GET', $lookupUrl, $secret);
            if ($lookupBody === null) {
                return back()->with('error', 'ECMR lookup request failed: system curl error.');
            }
            $queryresponse = json_decode($lookupBody);

            //store the search result in database
            $ecmr               = new ecmr();
            $ecmr->licence_plate = $ecmr_check;
            $ecmr->response     = $lookupBody;
            $ecmr->status       = $queryresponse->data->cmr_status ?? 'Unknown';
            $ecmr->cmr_number   = $queryresponse->data->cmr_number ?? 'N/A';
            $ecmr->message      = $queryresponse->message ?? '';
            $ecmr->policy_id    = $policy ? $policy->id : null;
            $ecmr->cuid         = $user ? $user->id : null;
            $ecmr->save();

            return back()->with('success', 'CMR information retrieved and stored successfully.');
        } else {
            return back()->with('error', 'ECMR login failed: ' . ($jsonObject->message ?? 'Unknown error'));
        }
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

    /**
     * Make an HTTP call using the system curl binary instead of PHP's cURL
     * extension, bypassing Namecheap's stale CA bundle entirely.
     * Returns the response body string, or null on failure.
     */
    private function curlExec(string $method, string $url, string $secret): ?string
    {
        $escapedUrl    = escapeshellarg($url);
        $escapedSecret = escapeshellarg('X-Proxy-Secret: ' . $secret);
        $methodFlag    = strtoupper($method) === 'POST' ? '-X POST' : '-X GET';

        $cmd    = "curl -s --max-time 30 {$methodFlag} {$escapedUrl} -H {$escapedSecret} -H " . escapeshellarg('Accept: application/json');
        $output = shell_exec($cmd);

        return ($output !== null && $output !== '') ? $output : null;
    }
}
