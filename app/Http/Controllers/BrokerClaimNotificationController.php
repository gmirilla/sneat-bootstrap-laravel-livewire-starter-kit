<?php

namespace App\Http\Controllers;

use App\Mail\ClaimNotificationMail;
use App\Models\ClaimAttachment;
use App\Models\ClaimNotification;
use App\Services\ProxyClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrokerClaimNotificationController extends Controller
{
    public function __construct(private readonly ProxyClient $proxy) {}

    public function index()
    {
        $claims = ClaimNotification::where('user_id', Auth::id())
            ->with('claimAttachments')
            ->latest()
            ->paginate(20);

        return view('broker.claims.index', compact('claims'));
    }

    public function create(Request $request)
    {
        $user     = Auth::user();
        $policies = $this->fetchPolicies($user->broker_id);
        $policy   = null;

        if ($request->filled('policy_no')) {
            $policy = collect($policies)->firstWhere('policy_no', $request->policy_no);
        }

        return view('broker.claims.create', compact('policies', 'policy'));
    }

    public function store(Request $request)
    {
        $user     = Auth::user();
        $policies = $this->fetchPolicies($user->broker_id);

        $request->validate([
            'policy_no'        => 'required|string|max:50',
            'incident_date'    => 'required|date|before_or_equal:today',
            'incident_time'    => 'nullable|date_format:H:i',
            'incident_location'=> 'nullable|string|max:200',
            'description'      => 'required|string|min:20|max:3000',
            'is_third_party'   => 'nullable|boolean',
            'claimant_name'    => 'required_if:is_third_party,1|nullable|string|max:100',
            'claimant_phone'   => 'nullable|string|max:20',
            'claimant_email'   => 'nullable|email|max:150',
            'attachments'      => 'nullable|array|max:5',
            'attachments.*'    => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Verify policy belongs to this broker's portfolio
        $policy = collect($policies)->firstWhere('policy_no', $request->policy_no);
        if (!$policy) {
            return back()->withInput()->withErrors(['policy_no' => 'Policy not found in your portfolio.']);
        }

        $isThirdParty = $request->boolean('is_third_party');

        $notification = ClaimNotification::create([
            'reference_no'      => $this->generateReference(),
            'policy_no'         => $policy['policy_no'],
            'policy_type'       => $policy['product_type'],
            'policy_start'      => $policy['date_from'],
            'policy_end'        => $policy['date_to'],
            'policy_source'     => 'elite_broker',
            'claimant_name'     => $isThirdParty ? $request->claimant_name : ($policy['name'] ?? $user->name),
            'claimant_email'    => $isThirdParty ? $request->claimant_email : null,
            'claimant_phone'    => $isThirdParty ? $request->claimant_phone : null,
            'is_third_party'    => $isThirdParty,
            'incident_date'     => $request->incident_date,
            'incident_time'     => $request->incident_time,
            'incident_location' => $request->incident_location,
            'description'       => $request->description,
            'user_id'           => $user->id,
            'status'            => 'received',
        ]);

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
            Mail::to($claimsEmail)->send(new ClaimNotificationMail($notification, false, false));
        }

        return redirect()->route('broker.claims.confirmation', $notification->reference_no)
            ->with('claim_submitted', true);
    }

    public function confirmation(string $referenceNo)
    {
        $notification = ClaimNotification::where('reference_no', $referenceNo)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('broker.claims.confirmation', compact('notification'));
    }

    public function downloadAttachment(ClaimAttachment $attachment)
    {
        $notification = $attachment->claimNotification ?? ClaimNotification::find($attachment->claim_notification_id);
        abort_if($notification?->user_id !== Auth::id(), 403);

        return response()->download(
            Storage::disk('local')->path($attachment->path),
            $attachment->original_name
        );
    }

    private function fetchPolicies(int $brokerId): array
    {
        $cacheKey = "elite_policies_{$brokerId}";
        $cached   = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        if (!$this->proxy->isConfigured()) return [];

        $raw = $this->proxy->call(
            'GET',
            $this->proxy->getBaseUrl() . '/api/elite/broker/policies?broker_id=' . $brokerId
        );
        if (!$raw) return [];

        $json = json_decode($raw, true);
        $data = ($json['status'] ?? '') === 'success' ? ($json['data'] ?? []) : [];

        if (!empty($data)) Cache::put($cacheKey, $data, 900);

        return $data;
    }

    private function generateReference(): string
    {
        do {
            $ref = 'CLN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (ClaimNotification::where('reference_no', $ref)->exists());

        return $ref;
    }
}
