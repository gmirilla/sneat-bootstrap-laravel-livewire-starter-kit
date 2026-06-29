<?php

namespace App\Http\Controllers;

use App\Models\BrokerTicket;
use App\Services\ProxyClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BrokerPolicyController extends Controller
{
    public function __construct(private readonly ProxyClient $proxy) {}

    public function index()
    {
        $user     = Auth::user();
        $policies = $this->fetchPolicies($user->broker_id);

        return view('broker.policies.index', compact('policies'));
    }

    public function show(string $policyNo)
    {
        $user     = Auth::user();
        $policies = $this->fetchPolicies($user->broker_id);
        $policy   = collect($policies)->firstWhere('policy_no', $policyNo);

        abort_if(!$policy, 404, 'Policy not found in your portfolio.');

        $tickets = BrokerTicket::where('user_id', $user->id)
            ->where('policy_no', $policyNo)
            ->with('latestMessage')
            ->latest()
            ->get();

        return view('broker.policies.show', compact('policy', 'tickets'));
    }

    private function fetchPolicies(int $brokerId): array
    {
        return Cache::remember("elite_policies_{$brokerId}", 900, function () use ($brokerId) {
            if (!$this->proxy->isConfigured()) return [];

            $raw = $this->proxy->call(
                'GET',
                $this->proxy->getBaseUrl() . '/api/elite/broker/policies?broker_id=' . $brokerId
            );

            if (!$raw) return [];

            $json = json_decode($raw, true);
            return ($json['status'] ?? '') === 'success' ? ($json['data'] ?? []) : [];
        });
    }
}
