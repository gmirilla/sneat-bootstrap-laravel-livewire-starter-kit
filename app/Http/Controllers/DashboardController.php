<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\agentsdetailsModel;
use App\Models\policy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\vehicleMake;
use App\Models\vehiclecolor;
use App\Models\states;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        Auth::check();
        $usercheck = Auth::user();
        $user = Auth::user();
        $products = policy::select('producttype')->distinct()->pluck('producttype');
        $agentslist = agentsdetailsModel::all();
        $query = Policy::query();
        $searchParams = $request->only(['policytype', 'status', 'datefrom', 'dateto', 'agentcode']);

        switch ($usercheck->role) {
            case in_array($usercheck->role, ['agent', 'subagent']):
                # code...
                # get credit left
                $agent = agentsdetailsModel::where('uid', $usercheck->id)->first();
                if ($usercheck->role == 'agent') {
                    $creditleft = $agent->noallocated - $agent->noused;
                } else {
                    $creditleft = $agent->subcreditassigned - $agent->subcreditused;
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

                $policies = $query->get();

                #get No of Policies
                $policygroup = policy::select('producttype', DB::raw('count(*) as total'))->where('status', 'approved')->where('agent_id', $usercheck->id)
                    ->groupBy('producttype')->get();


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
                    $searchParams['agentname'] = User::find($request->agentcode)->name ?? 'Unknown';
                }

                $policies = $query->get();

                #get No of Policies

                #get policies approaching renewal
                $approachingrenewal = policy::whereBetween('end_date', [now(), now()->addDays(30)])
                    ->where('status', 'approved')
                    ->count();

                break;
            case 'user':
                # code...
                $creditleft = 0;

                #get No of Policies
                $totalpolcount = policy::where('insured_id', $usercheck->id)->count();
                $totalpoldraft = policy::where('insured_id', $usercheck->id)->where('status', 'draft')->count();
                $totalpolfailed = policy::where('insured_id', $usercheck->id)->where('status', 'failed')->count();
                $totalpolapproved = policy::where('insured_id', $usercheck->id)->where('status', 'approved')->count();
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
        $totalpolcount = $policies->count();
        $totalpoldraft = $policies->where('status', 'draft')->count();
        $totalpolfailed = $policies->where('status', 'failed')->count();
        $totalpolapproved = $policies->where('status', 'approved')->count();
        $policygroup = policy::select('producttype', DB::raw('count(*) as total'))->where('status', 'approved')
            ->groupBy('producttype')->get();



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
            'searchParams'
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

    /**
     * Remove the niipmanagement dashboard.
     */
    public function niipmgtdashboard()
    {
        return view('niip.niipcodemgmt', [
            'totalMakes' => vehicleMake::count(),
            'totalColors' => vehicleColor::count(),
            'totalStates' => states::count(),
        ]);
    }
}
