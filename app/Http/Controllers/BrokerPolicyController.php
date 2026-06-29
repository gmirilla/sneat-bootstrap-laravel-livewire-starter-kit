<?php

namespace App\Http\Controllers;

use App\Models\BrokerTicket;
use App\Services\ProxyClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BrokerPolicyController extends Controller
{
    public function __construct(private readonly ProxyClient $proxy) {}

    public function index(Request $request)
    {
        $user  = Auth::user();
        $all   = $this->fetchPolicies($user->broker_id);
        $today = Carbon::today();
        $in30  = Carbon::today()->addDays(30);

        $collection = collect($all);

        $counts = [
            'all'      => $collection->count(),
            'active'   => $collection->filter(fn($p) => Carbon::parse($p['date_to'])->gte($today))->count(),
            'expiring' => $collection->filter(fn($p) => Carbon::parse($p['date_to'])->between($today, $in30))->count(),
        ];

        $view = in_array($request->get('view'), ['active', 'expiring']) ? $request->get('view') : 'all';

        $policies = match ($view) {
            'active'   => $collection->filter(fn($p) => Carbon::parse($p['date_to'])->gte($today))->values()->all(),
            'expiring' => $collection->filter(fn($p) => Carbon::parse($p['date_to'])->between($today, $in30))->values()->all(),
            default    => $all,
        };

        return view('broker.policies.index', compact('policies', 'view', 'counts'));
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
