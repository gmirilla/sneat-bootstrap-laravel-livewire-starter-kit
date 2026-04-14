<?php

namespace App\Http\Controllers;

use App\Mail\ClaimEnquiry;
use App\Models\claim;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;


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
    $claimNumber = $request->claimnumber;

    $secret   = env('PROXY_SECRET');
    $proxyUrl = rtrim(env('PROXY_URL'), '/');

    if (empty($secret) || empty($proxyUrl)) {
        $response = ['status' => 'error', 'message' => 'Proxy not configured. Add PROXY_SECRET and PROXY_URL to .env', 'data' => []];
        return view('claim.claim_check', compact('response'));
    }

    $claimUrl = $proxyUrl . '/api/claim/check?number=' . urlencode($claimNumber);

    if (PHP_OS_FAMILY === 'Windows') {
        // Local dev: use Laravel Http client
        try {
            $httpResponse = Http::withHeaders([
                'X-Proxy-Secret' => $secret,
                'Accept'         => 'application/json',
            ])->timeout(30)->get($claimUrl);
            $body = $httpResponse->body();
        } catch (\Exception $e) {
            Log::error('Claim Check Error: Http client exception — ' . $e->getMessage());
            $body = null;
        }
    } else {
        // Linux/Namecheap: bypass PHP cURL's stale CA bundle via system curl
        $escapedUrl    = escapeshellarg($claimUrl);
        $escapedSecret = escapeshellarg('X-Proxy-Secret: ' . $secret);
        $cmd           = "curl -s --max-time 30 -X GET {$escapedUrl} -H {$escapedSecret} -H " . escapeshellarg('Accept: application/json');
        $body          = shell_exec($cmd);
    }

    if (empty($body)) {
        Log::error('Claim Check Error: proxy returned empty response');
        $response = ['status' => 'error', 'message' => 'Unable to reach claim service at this time.', 'data' => []];
        return view('claim.claim_check', compact('response'));
    }

    $response = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        Log::error('Claim Check Error: invalid JSON from proxy — ' . $body);
        $response = ['status' => 'error', 'message' => 'Invalid response from claim service.', 'data' => []];
    }

    return view('claim.claim_check', compact('response'));
}

    /**
     * Send a claim enquiry email to the claims team.
     */
    public function sendEnquiry(Request $request)
    {
        $request->validate([
            'sender_name'  => 'required|string|max:100',
            'sender_email' => 'required|email',
            'message_body' => 'required|string|max:2000',
            'documents'    => 'nullable|array|max:5',
            'documents.*'  => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        // Store uploads in temp disk; collect absolute paths for the Mailable
        $attachmentPaths = [];
        foreach ($request->file('documents', []) as $file) {
            $attachmentPaths[] = $file->store('claim_enquiries', 'local');
        }

        // Resolve to absolute paths so Attachment::fromPath() can read them
        $absPaths = array_map(
            fn(string $p) => storage_path('app/' . $p),
            $attachmentPaths
        );

        try {
            Mail::to(env('CLAIMS_EMAIL', 'claims@salamtakafulinsurance.com'))
                ->send(new ClaimEnquiry(
                    senderName:      $request->sender_name,
                    senderEmail:     $request->sender_email,
                    claimNo:         $request->claim_no  ?: null,
                    policyNo:        $request->policy_no ?: null,
                    messageBody:     $request->message_body,
                    attachmentPaths: $absPaths,
                ));

            // Clean up temp files after successful send
            foreach ($attachmentPaths as $path) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
            }

            return back()->with('enquiry_success', 'Your message has been sent to the claims team.');
        } catch (\Exception $e) {
            Log::error('Claim enquiry email failed: ' . $e->getMessage());
            return back()->with('enquiry_error', 'Failed to send message. Please try again later.');
        }
    }
}
