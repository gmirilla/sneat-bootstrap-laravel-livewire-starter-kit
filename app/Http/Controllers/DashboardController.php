<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\agentsdetailsModel;
use App\Models\BrokerTicket;
use App\Models\policy;
use App\Models\User;
use App\Services\ProxyClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\vehicleMake;
use App\Models\vehiclecolor;
use App\Models\states;

class DashboardController extends Controller
{
    public function __construct(private readonly ProxyClient $proxy) {}

    public function index(Request $request)
    {
        //
        Auth::check();
        $usercheck = Auth::user();
        $user = Auth::user();
        $products = policy::select('producttype')->distinct()->pluck('producttype');
        $agentslist = agentsdetailsModel::all();
        $query    = Policy::query();
        $policies = collect();   // default; overwritten by agent/admin switch cases
        $searchParams = $request->only(['policytype', 'status', 'datefrom', 'dateto', 'agentcode']);

        // Broker gets a completely different dashboard driven by the Elite API
        if ($usercheck->role === 'broker') {
            return view('dashboardnew', array_merge(
                $this->buildBrokerDashboard($usercheck),
                [
                    'user'           => $user,
                    'usercheck'      => $usercheck,
                    'products'       => $products,
                    'agentslist'     => collect(),
                    'searchParams'   => [],
                    'answers'        => [],
                    'policygroup'    => collect(),
                    'creditleft'     => 0,
                    'creditassigned' => 0,
                    'creditused'     => 0,
                    'totalpolcount'  => 0,
                    'totalpoldraft'  => 0,
                    'totalpolfailed' => 0,
                    'totalpolapproved' => 0,
                    'approachingrenewal' => 0,
                ]
            ));
        }

        switch ($usercheck->role) {
            case in_array($usercheck->role, ['agent', 'subagent']):
                # code...
                # get credit left
                $agent = agentsdetailsModel::where('uid', $usercheck->id)->first();
                if ($usercheck->role == 'agent') {
                    $creditleft = $agent->noallocated - $agent->noused;
                    $creditassigned = $agent->noallocated;
                    $creditused = $agent->noused;
                } else {
                    $creditleft = $agent->subcreditassigned - $agent->subcreditused;
                    $creditassigned = $agent->subcreditassigned;
                    $creditused = $agent->subcreditused;
                }


                if ($request->filled('policytype')) {
                    $query->where('producttype', $request->policytype)->where('agent_id', $user->id);
                }

                if ($request->filled('status')) {
                    $query->where('status', $request->status)->where('agent_id', $user->id);
                }

                if ($request->filled('datefrom')) {
                    $query->whereDate('created_at', '>=', $request->datefrom)->where('agent_id', $user->id);
                }

                if ($request->filled('dateto')) {
                    $query->whereDate('created_at', '<=', $request->dateto)->where('agent_id', $user->id);
                }
                if ($request->filled('agentcode')) {
                    $query->where('agent_id', $request->agentcode)->where('agent_id', $user->id);
                    $searchParams['agentname'] = User::find($request->agentcode)->name ?? 'Unknown';
                }

                $policies = $query->where('agent_id', $user->id)->get();


                #get No of Policies
                $policygroup = policy::select('producttype', DB::raw('count(*) as total'))->where('status', 'approved')->where('agent_id', $usercheck->id)
                    ->groupBy('producttype')->get();
                //dd($policygroup);//


                #get policies approaching renewal
                $approachingrenewal = policy::where('agent_id', $usercheck->id)
                    ->whereBetween('end_date', [now(), now()->addDays(30)])
                    ->where('status', 'approved')
                    ->count();


                break;
            case in_array($user->role, ['admin', 'superadmin']):
                # code...
                # get credit left
                $creditleft = 0;
                $creditleft = 0;
                $creditassigned = 0;
                $creditused = 0;
                if ($request->filled('policytype')) {
                    $query->where('producttype', $request->policytype);
                }

                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }

                if ($request->filled('datefrom')) {
                    $query->whereDate('created_at', '>=', $request->datefrom);
                }

                if ($request->filled('dateto')) {
                    $query->whereDate('created_at', '<=', $request->dateto);
                }
                if ($request->filled('agentcode')) {
                    $query->where('agent_id', $request->agentcode);
                    $agent = agentsdetailsModel::where('uid', $request->agentcode)->first();
                    $agentcheck = User::find($request->agentcode);
                    if ($agentcheck->role == 'agent') {
                        $creditleft = $agent->noallocated - $agent->noused;
                        $creditassigned = $agent->noallocated;
                        $creditused = $agent->noused;
                    } else {
                        $creditleft = $agent->subcreditassigned - $agent->subcreditused;
                        $creditassigned = $agent->subcreditassigned;
                        $creditused = $agent->subcreditused;
                    }

                    $searchParams['agentname'] = User::find($request->agentcode)->name ?? 'Unknown';
                }

                $policies = $query->get();
                $policygroup = policy::select('producttype', DB::raw('count(*) as total'))->where('status', 'approved')
                    ->groupBy('producttype')->get();

                #get No of Policies

                #get policies approaching renewal
                $approachingrenewal = policy::whereBetween('end_date', [now(), now()->addDays(30)])
                    ->where('status', 'approved')
                    ->count();

                break;
            case 'user':
                # code...
                $creditleft = 0;
                $creditassigned = 0;
                $creditused = 0;

                #get No of Policies
                $policygroup = policy::select('producttype', DB::raw('count(*) as total'))->where('status', 'approved')->where('insured_id', $usercheck->id)
                    ->groupBy('producttype')->get();


                #get policies approaching renewal
                $approachingrenewal = policy::whereBetween('end_date', [now(), now()->addDays(30)])->where('insured_id', $usercheck->id)
                    ->where('status', 'approved')
                    ->count();
            case 'broker':
                # code...
                $creditleft = 0;
                $creditassigned = 0;
                $creditused = 0;

                #get No of Policies
                $policygroup = policy::select('producttype', DB::raw('count(*) as total'))->where('status', 'approved')->where('insured_id', $usercheck->id)
                    ->groupBy('producttype')->get();


                #get policies approaching renewal
                $approachingrenewal = policy::whereBetween('end_date', [now(), now()->addDays(30)])->where('insured_id', $usercheck->id)
                    ->where('status', 'approved')
                    ->count();
                break;
            default:
                # code...
                break;
        }

        if (in_array($usercheck->role, ['user', 'broker'])) {

            $baseQuery = Policy::where('insured_id', $usercheck->id);

            $totalpolcount     = $baseQuery->count();

            $statusCounts = $baseQuery->select('status', DB::raw('COUNT(*) as total'))
                ->whereIn('status', ['draft', 'failed', 'approved'])
                ->groupBy('status')
                ->pluck('total', 'status');

            $totalpoldraft     = $statusCounts['draft'] ?? 0;
            $totalpolfailed    = $statusCounts['failed'] ?? 0;
            $totalpolapproved  = $statusCounts['approved'] ?? 0;
        } else {
            $totalpolcount = $policies->count();
            $totalpoldraft = $policies->where('status', 'draft')->count();
            $totalpolfailed = $policies->where('status', 'failed')->count();
            $totalpolapproved = $policies->where('status', 'approved')->count();
        }





        #Build a matrix to handle display

        $allagents = policy::select('agent_id', 'producttype', DB::raw('count(*) as total'))->where('status', 'approved')
            ->groupBy('agent_id', 'producttype')->orderBy('agent_id')->get();



        $counter = 0;
        $answers = [0];
        foreach ($allagents as $agent) {
            # code...
            #Temp solution to get agent name
            $agentname = DB::table('users')->where('id', $agent->agent_id)->value('name');
            if ($agentname) {
                $agent->agent_name = $agentname;
            } else {
                $agent->agent_name = 'Unknown Agent';
            }
            #End of temp solution
            #Build the report
            $report = [
                'agent_id' => $agent->agent_id,
                'agent_name' => $agent->agent_name,
                'total_sale' => $agent->total,
                'producttype' => $agent->producttype

            ];

            # code...
            $answers[$counter] = $report;
            $counter = $counter + 1;
        }


            #End of report build
            /* Debug renewal date
                    $renewals = policy::where('end_date', '<=', $approachingrenewaldate)
                        ->where('status', 'approved')
                        ->get(); 
                        echo $approachingrenewaldate;
                        dd($renewals);

                    
                    */
            //dd($totalpolcount)
        ;
        return view('dashboardnew', compact(
            'creditleft',
            'totalpolcount',
            'totalpoldraft',
            'totalpolfailed',
            'totalpolapproved',
            'policygroup',
            'usercheck',
            'answers',
            'approachingrenewal',
            'products',
            'user',
            'agentslist',
            'searchParams',
            'creditassigned',
            'creditused'
        ));
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    private function buildBrokerDashboard(User $user): array
    {
        $brokerId = $user->broker_id;

        $cacheKey = "elite_policies_{$brokerId}";
        $policies = Cache::get($cacheKey);

        if ($policies === null) {
            $policies = [];
            if ($this->proxy->isConfigured()) {
                $raw = $this->proxy->call(
                    'GET',
                    $this->proxy->getBaseUrl() . '/api/elite/broker/policies?broker_id=' . $brokerId
                );
                if ($raw) {
                    $json     = json_decode($raw, true);
                    $policies = ($json['status'] ?? '') === 'success' ? ($json['data'] ?? []) : [];
                }
            }
            if (!empty($policies)) {
                Cache::put($cacheKey, $policies, 900);
            }
        }

        $collection = collect($policies);
        $today      = Carbon::today();
        $in30       = Carbon::today()->addDays(30);

        $activeCollection = $collection->filter(fn($p) => Carbon::parse($p['date_to'])->gte($today));

        $totalPolicies  = $collection->count();
        $activePolicies = $activeCollection->count();
        $activePremium  = $activeCollection->sum(fn($p) => (float) ($p['actual_gross_premium_lc'] ?? 0));
        $expiring30     = $collection->filter(
            fn($p) => Carbon::parse($p['date_to'])->between($today, $in30)
        )->count();
        $openTickets    = BrokerTicket::where('user_id', $user->id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        // Doughnut: portfolio by product type
        $portfolioByType = $collection
            ->groupBy('product_type')
            ->map->count()
            ->sortDesc()
            ->all();

        // Bar: expirations bucketed into the next 6 calendar months
        $expiryByMonth = [];
        for ($i = 0; $i < 6; $i++) {
            $expiryByMonth[Carbon::today()->addMonths($i)->format('M Y')] = 0;
        }
        foreach ($policies as $p) {
            $expDate = Carbon::parse($p['date_to']);
            if ($expDate->gte($today) && $expDate->lte(Carbon::today()->addMonths(6))) {
                $key = $expDate->format('M Y');
                if (array_key_exists($key, $expiryByMonth)) {
                    $expiryByMonth[$key]++;
                }
            }
        }

        return [
            'brokerTotalPolicies'   => $totalPolicies,
            'brokerActivePolicies'  => $activePolicies,
            'brokerActivePremium'   => $activePremium,
            'brokerExpiring30'      => $expiring30,
            'brokerOpenTickets'     => $openTickets,
            'brokerPortfolioByType' => $portfolioByType,
            'brokerExpiryByMonth'   => $expiryByMonth,
            'brokerApiAvailable'    => !empty($policies),
        ];
    }

    /**
     * Remove the niipmanagement dashboard.
     */
    public function niipmgtdashboard()
    {
        // Count policies with definite NIIP failures (DB-level patterns only;
        // JSON-response failures are shown separately in the view if needed).
        $failedNiipCount = policy::where('status', 'approved')
            ->whereNotNull('policyno')
            ->where('policyno', '!=', '')
            ->where(function ($q) {
                $q->whereNull('niip_status')
                    ->orWhere('niip_status', '')
                    ->orWhere('niip_status', 'Array')
                    ->orWhere('niip_status', 'like', 'Error:%')
                    ->orWhere('niip_status', 'retry_queued');
            })
            ->count();

        return view('niip.niipcodemgmt', [
            'totalMakes'      => vehicleMake::count(),
            'totalColors'     => vehiclecolor::count(),
            'totalStates'     => states::count(),
            'failedNiipCount' => $failedNiipCount,
        ]);
    }
}
