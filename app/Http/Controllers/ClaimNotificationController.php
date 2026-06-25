<?php

namespace App\Http\Controllers;

use App\Mail\ClaimAccountCreatedMail;
use App\Mail\ClaimNotificationAdminMail;
use App\Mail\ClaimNotificationMail;
use App\Models\ClaimNotification;
use App\Models\policy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ClaimNotificationController extends Controller
{
    public function showLookup()
    {
        return view('claim.notify.lookup');
    }

    public function lookupPolicy(Request $request)
    {
        $request->validate([
            'policy_no' => 'required|string|max:50',
            'phone'     => 'required|string|max:20',
        ]);

        $policyNo = trim($request->policy_no);
        $phone    = trim($request->phone);

        // 1. Try local DB first
        $local = policy::where('policyno', $policyNo)
            ->where('status', 'approved')
            ->whereHas('insuredUser', fn($q) => $q->where('telno', $phone))
            ->select('policyno', 'producttype', 'start_date', 'end_date')
            ->first();

        if ($local) {
            $request->session()->put('claim_lookup', [
                'policy_no'   => $local->policyno,
                'policy_type' => $local->producttype,
                'start_date'  => $local->start_date,
                'end_date'    => $local->end_date,
                'source'      => 'local',
                'expires_at'  => now()->addMinutes(30)->timestamp,
            ]);

            return redirect()->route('claim.notify.form');
        }

        // 2. Fall back to Elite API
        $eliteData = $this->fetchFromElite($policyNo, $phone);

        if ($eliteData) {
            $request->session()->put('claim_lookup', [
                'policy_no'   => $eliteData['policy_no'],
                'policy_type' => $eliteData['policy_type'],
                'start_date'  => $eliteData['start_date'],
                'end_date'    => $eliteData['end_date'],
                'source'      => 'remote',
                'expires_at'  => now()->addMinutes(30)->timestamp,
            ]);

            return redirect()->route('claim.notify.form');
        }

        return back()->with('lookup_error', 'No active policy found matching this policy number and phone number. Please check your details and try again.');
    }

    public function showForm(Request $request)
    {
        $lookup = $request->session()->get('claim_lookup');

        if (!$lookup || $lookup['expires_at'] < now()->timestamp) {
            $request->session()->forget('claim_lookup');
            return redirect()->route('claim.notify.lookup')
                ->with('lookup_error', 'Your session has expired. Please look up your policy again.');
        }

        return view('claim.notify.form', compact('lookup'));
    }

    public function submit(Request $request)
    {
        $lookup = $request->session()->get('claim_lookup');

        if (!$lookup || $lookup['expires_at'] < now()->timestamp) {
            $request->session()->forget('claim_lookup');
            return redirect()->route('claim.notify.lookup')
                ->with('lookup_error', 'Your session expired before the form could be submitted. Please start again.');
        }

        $request->validate([
            'claimant_name'  => 'required|string|max:100',
            'claimant_email' => 'required|email|max:150',
            'claimant_phone' => 'required|string|max:20',
            'incident_date'  => 'required|date|before_or_equal:today',
            'description'    => 'required|string|min:20|max:3000',
        ]);

        $reference      = $this->generateReference();
        $accountCreated = false;
        $accountPending = false;

        $existingUser = User::where('email', $request->claimant_email)->first();

        if ($existingUser) {
            $userId         = $existingUser->id;
            $accountPending = $existingUser->account_status === 'pending';
        } else {
            $nameParts = explode(' ', trim($request->claimant_name), 2);
            $newUser = User::create([
                'name'           => $request->claimant_name,
                'firstname'      => $nameParts[0],
                'lastname'       => $nameParts[1] ?? '',
                'email'          => $request->claimant_email,
                'telno'          => $request->claimant_phone,
                'password'       => Hash::make(Str::random(24)),
                'role'           => 'user',
                'account_status' => 'pending',
            ]);

            $userId         = $newUser->id;
            $accountCreated = true;
        }

        $notification = ClaimNotification::create([
            'reference_no'   => $reference,
            'policy_no'      => $lookup['policy_no'],
            'policy_type'    => $lookup['policy_type'],
            'policy_start'   => $lookup['start_date'],
            'policy_end'     => $lookup['end_date'],
            'policy_source'  => $lookup['source'],
            'claimant_name'  => $request->claimant_name,
            'claimant_email' => $request->claimant_email,
            'claimant_phone' => $request->claimant_phone,
            'incident_date'  => $request->incident_date,
            'description'    => $request->description,
            'user_id'        => $userId,
            'status'         => 'submitted',
        ]);

        // Send notification to claims team
        Mail::to(config('variables.CLAIMS_EMAIL'))
            ->send(new ClaimNotificationMail($notification, $accountCreated, $accountPending));

        // If a new account was created, notify claimant and admin separately
        if ($accountCreated) {
            Mail::to($request->claimant_email)->send(new ClaimAccountCreatedMail($newUser, $reference));
            Mail::to(config('variables.CLAIMS_EMAIL'))->send(new ClaimNotificationAdminMail($newUser, $notification));
        }

        $request->session()->forget('claim_lookup');

        return view('claim.notify.confirmation', compact('notification', 'accountCreated'));
    }

    private function fetchFromElite(string $policyNo, string $phone): ?array
    {
        $baseUrl = rtrim(config('variables.API_ELITE_URL', ''), '/');

        if (empty($baseUrl)) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($baseUrl . '/customer-lookup', [
                'policy_no' => $policyNo,
                'phone'     => $phone,
            ]);

            $data = $response->json();

            if (!($data['found'] ?? false)) {
                return null;
            }

            return $data['data'];

        } catch (\Exception $e) {
            Log::error('ClaimNotificationController: Elite lookup failed', [
                'policy_no' => $policyNo,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function generateReference(): string
    {
        do {
            $ref = 'CLN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (ClaimNotification::where('reference_no', $ref)->exists());

        return $ref;
    }
}
