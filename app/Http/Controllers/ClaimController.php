<?php

namespace App\Http\Controllers;

use App\Mail\ClaimEnquiry;
use App\Services\ProxyClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ClaimController extends Controller
{
    public function claimcheck(Request $request, ProxyClient $proxy)
    {
        $request->validate([
            'claimnumber' => 'required|string|max:50',
        ]);

        $claimNumber = $request->claimnumber;

        if (!$proxy->isConfigured()) {
            $claimData = ['status' => 'error', 'message' => 'Proxy not configured. Add PROXY_SECRET and PROXY_URL to .env', 'data' => []];
            return view('claim.claim_check', compact('claimData'));
        }

        $body = $proxy->call('GET', $proxy->getBaseUrl() . '/api/claim/check?number=' . urlencode($claimNumber));

        if (empty($body)) {
            Log::error('Claim Check Error: proxy returned empty response');
            $claimData = ['status' => 'error', 'message' => 'Unable to reach claim service at this time.', 'data' => []];
            return view('claim.claim_check', compact('claimData'));
        }

        $claimData = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Claim Check Error: invalid JSON from proxy', ['body' => $body]);
            $claimData = ['status' => 'error', 'message' => 'Invalid response from claim service.', 'data' => []];
        }

        return view('claim.claim_check', compact('claimData'));
    }

    public function sendEnquiry(Request $request)
    {
        $request->validate([
            'sender_name'  => 'required|string|max:100',
            'sender_email' => 'required|email',
            'message_body' => 'required|string|max:2000',
            'documents'    => 'nullable|array|max:5',
            'documents.*'  => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $attachmentPaths = [];
        foreach ($request->file('documents', []) as $file) {
            $attachmentPaths[] = $file->store('claim_enquiries', 'local');
        }

        $absPaths = array_map(
            fn(string $p) => storage_path('app/' . $p),
            $attachmentPaths
        );

        try {
            Mail::to(config('variables.CLAIMS_EMAIL'))
                ->send(new ClaimEnquiry(
                    senderName:      $request->sender_name,
                    senderEmail:     $request->sender_email,
                    claimNo:         $request->claim_no  ?: null,
                    policyNo:        $request->policy_no ?: null,
                    messageBody:     $request->message_body,
                    attachmentPaths: $absPaths,
                ));

            return back()->with('enquiry_success', 'Your message has been sent to the claims team.');
        } catch (\Exception $e) {
            Log::error('Claim enquiry email failed: ' . $e->getMessage());
            return back()->with('enquiry_error', 'Failed to send message. Please try again later.');
        } finally {
            foreach ($attachmentPaths as $path) {
                Storage::disk('local')->delete($path);
            }
        }
    }
}
