<?php

namespace App\Http\Controllers;

use App\Mail\ClaimAccountCreatedMail;
use App\Mail\ClaimNotificationAdminMail;
use App\Mail\ClaimNotificationMail;
use App\Mail\ClaimRegisteredMail;
use App\Models\ClaimAttachment;
use App\Models\ClaimNotification;
use App\Models\policy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
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

            'email' => 'nullable|email|max:150|required_without:phone',
            'phone' => 'nullable|string|max:20|required_without:email',
        ]);

        $policyNo = trim($request->policy_no);
        $email    = filled($request->email) ? trim($request->email) : null;
        $phone    = filled($request->phone) ? trim($request->phone) : null;

        // 1. Try local DB first
        $local = policy::where('policyno', $policyNo)
            ->where('status', 'approved')
            ->whereHas('insuredUser', function ($q) use ($email, $phone) {
                $q->where(function ($inner) use ($email, $phone) {
                    if ($email) $inner->orWhere('email', $email);
                    if ($phone) $inner->orWhere('telno', $phone);
                });
            })
            ->with('insuredUser:id,name,email,telno')
            ->select('policyno', 'producttype', 'start_date', 'end_date', 'insured_id')
            ->first();

        if ($local) {
            $insured = $local->insuredUser;
            $request->session()->put('claim_lookup', [
                'policy_no'      => $local->policyno,
                'policy_type'    => $local->producttype,
                'start_date'     => $local->start_date,
                'end_date'       => $local->end_date,
                'source'         => 'local',
                'expires_at'     => now()->addMinutes(30)->timestamp,
                'claimant_name'  => $insured?->name,
                'claimant_email' => $insured?->email,
                'claimant_phone' => $insured?->telno,
            ]);

            return redirect()->route('claim.notify.form');
        }

        // 2. Fall back to Elite API
        $eliteData = $this->fetchFromElite($policyNo, $email, $phone);

        if ($eliteData) {
            $request->session()->put('claim_lookup', [
                'policy_no'      => $eliteData['policy_no'],
                'policy_type'    => $eliteData['policy_type'],
                'start_date'     => $eliteData['start_date'],
                'end_date'       => $eliteData['end_date'],
                'source'         => 'remote',
                'expires_at'     => now()->addMinutes(30)->timestamp,
                'claimant_name'  => $eliteData['name']  ?? null,
                'claimant_email' => $eliteData['email'] ?? null,
                'claimant_phone' => $eliteData['phone'] ?? null,
            ]);

            return redirect()->route('claim.notify.form');
        }

        return back()->with('lookup_error', 'No active policy found matching this policy number and email address. Please check your details and try again.');
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
            'attachments'    => 'nullable|array|max:5',
            'attachments.*'  => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $reference      = $this->generateReference();
        $accountCreated = false;
        $accountPending = false;
        $newUser        = null;

        $existingUser = User::where('email', $request->claimant_email)->first();

        if ($existingUser) {
            if ($existingUser->account_status === 'rejected') {
                $existingUser->update(['account_status' => 'pending']);
                $existingUser->refresh();
                $userId         = $existingUser->id;
                $accountCreated = true;
                $newUser        = $existingUser;
            } else {
                $userId         = $existingUser->id;
                $accountPending = $existingUser->account_status === 'pending';
            }
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
            'status'         => 'received',
        ]);

        // Store attachments in private disk — not publicly accessible
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("claim-attachments/{$notification->id}", 'local');
                ClaimAttachment::create([
                    'claim_notification_id' => $notification->id,
                    'original_name'         => $file->getClientOriginalName(),
                    'path'                  => $path,
                    'mime_type'             => $file->getMimeType(),
                    'size'                  => $file->getSize(),
                ]);
            }
        }

        $claimsEmail = config('variables.CLAIMS_EMAIL') ?: null;
        if ($claimsEmail) {
            Mail::to($claimsEmail)
                ->send(new ClaimNotificationMail($notification, $accountCreated, $accountPending));
        }

        if ($accountCreated) {
            Mail::to($request->claimant_email)->send(new ClaimAccountCreatedMail($newUser, $reference));
            if ($claimsEmail) {
                Mail::to($claimsEmail)->send(new ClaimNotificationAdminMail($newUser, $notification));
            }
        }

        $request->session()->forget('claim_lookup');

        $request->session()->flash('claim_confirmation', [
            'notification_id' => $notification->id,
            'account_created' => $accountCreated,
        ]);

        return redirect()->route('claim.notify.confirmation');
    }

    public function showConfirmation(Request $request)
    {
        $data = $request->session()->get('claim_confirmation');

        if (!$data) {
            return redirect()->route('claim.notify.lookup');
        }

        $notification   = ClaimNotification::findOrFail($data['notification_id']);
        $accountCreated = $data['account_created'];

        return view('claim.notify.confirmation', compact('notification', 'accountCreated'));
    }

    public function listNotifications(Request $request)
    {
        $user    = Auth::user();
        $isAdmin = in_array($user->role, ['admin', 'superadmin']);

        $query = ClaimNotification::query()
            ->with(['user', 'claimAttachments'])
            ->withCount('claimAttachments')
            ->latest();

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($isAdmin && $request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference_no', 'like', "%{$s}%")
                    ->orWhere('policy_no', 'like', "%{$s}%")
                    ->orWhere('elite_claim_no', 'like', "%{$s}%")
                    ->orWhere('claimant_name', 'like', "%{$s}%")
                    ->orWhere('claimant_email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $notifications = $query->paginate(15)->withQueryString();

        return view('claim.notify.list', compact('notifications', 'isAdmin'));
    }

    public function updateStatus(Request $request, ClaimNotification $notification)
    {
        if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $request->validate(['status' => 'required|in:received,registered,closed']);

        if ($request->status === 'registered' && !$notification->elite_claim_no) {
            return back()->with('error', "Cannot mark claim {$notification->reference_no} as Registered without an Elite Claim Number. Record the Elite Claim Number first.");
        }

        $notification->update(['status' => $request->status]);

        return back()->with('success', "Claim {$notification->reference_no} marked as {$request->status}.");
    }

    public function recordEliteClaimNo(Request $request, ClaimNotification $notification)
    {
        if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $request->validate([
            'elite_claim_no' => [
                'required',
                'string',
                'max:50',
                // unique across all rows except this one
                "unique:claim_notifications,elite_claim_no,{$notification->id}",
            ],
        ]);

        $isFirstAssignment = empty($notification->elite_claim_no);

        $notification->update([
            'elite_claim_no' => $request->elite_claim_no,
            'status'         => 'registered',
        ]);

        if ($isFirstAssignment) {
            Mail::to($notification->claimant_email)->send(new ClaimRegisteredMail($notification));
        }

        return back()->with('success', "Elite Claim Number recorded for {$notification->reference_no}. Claimant has been notified.");
    }

    public function downloadAttachment(ClaimAttachment $attachment)
    {
        $user    = Auth::user();
        $isAdmin = in_array($user->role, ['admin', 'superadmin']);
        $isOwner = $attachment->claimNotification->user_id === $user->id;

        if (!$isAdmin && !$isOwner) {
            abort(403);
        }

        $fullPath = Storage::disk('local')->path($attachment->path);
        return response()->download($fullPath, $attachment->original_name);
    }

    private function fetchFromElite(string $policyNo, ?string $email, ?string $phone): ?array
    {
        $baseUrl = rtrim(config('variables.PROXY_URL', ''), '/');

        if (empty($baseUrl)) {
            return null;
        }

        $params = ['policy_no' => $policyNo];
        if (!empty($email)) $params['email'] = $email;
        if (!empty($phone)) $params['phone'] = $phone;

        try {
            $response = Http::timeout(15)->get($baseUrl . '/api/v1/policy/customer-lookup', $params);

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
