<?php

namespace App\Http\Controllers;

use App\Models\agentsdetailsModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentsdetailsModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $agents = DB::table('users')
            ->leftJoin('agentsdetails_models', 'users.id', '=', 'agentsdetails_models.uid')
            ->select(
                'users.id as id',
                'name',
                'email',
                'allowcredit',
                'noallocated',
                'noused',
                'status',
                'auth_token'

            )->where('role', 'agent')->get();


        return view('usermgmgt.listagent', compact('agents'));
    }



    public function agentprofile(Request $request)
    {
        //
        // dd($request);
        $user = User::where('id', $request->uid)->first();
        $agent = agentsdetailsModel::where('uid', $request->uid)->first();

        return view('usermgmgt.agentdetails', compact('user', 'agent'));
    }

        public function subagentsprofile(User $sid)
    {
        //
        $user = $sid;
        $agent = agentsdetailsModel::where('uid', $sid->id)->first();

        return view('subagents.profile', compact('user', 'agent'));
    }


    public function agentupdate(Request $request)
    {
        //
        try {
            //code...
            $agent = agentsdetailsModel::where('uid', $request->userid)->first();
            if (!$agent) {
                $agent = new agentsdetailsModel();
                $agent->uid = $request->userid;
            }

            $agent->noallocated = $request->noallocated;
            $agent->status = $request->status;
            $agent->auth_token = $request->authtoken;
            $agent->canregistersubagent= $request->has('agentregistersubagentchk') ? true : false;


            if ($request->has('agentcreditchk')) {

                $agent->allowcredit = true;
            } else {

                $agent->allowcredit = false;
            }

            $agent->save();
        } catch (\Throwable $th) {
            //throw $th;
        }


        return redirect()->route('list_agents');
    }


    //LIST ALL SUB AGENTS BY AGENT
    public function subagentsList(Request $request)
    {
        $agent        = Auth::user();
        $subagents    = User::where('parentid', $agent->id)->get();
        $agentDetails = agentsdetailsModel::where('uid', $agent->id)->first();
        $availableCredits = $agentDetails->noallocated - $agentDetails->noused;

        return view('subagents.subagentslist', compact('subagents', 'agent', 'availableCredits', 'agentDetails'));
    }

    //REGISTER NEW SUB AGENTS BY AGENT
    public function registerSubAgent(User $agent, Request $request)
    {
        $agentDetails = agentsdetailsModel::where('uid', $agent->id)->first();
        $poolMode     = $agentDetails && $agentDetails->pool_enabled;

        $validatedData = $request->validate([
            'firstname'  => 'required',
            'lastname'   => 'required',
            'phone'      => 'required',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6',
            // In pool mode this becomes an optional cap (0 = unlimited).
            // In individual mode it is the allocated credit amount.
            'subcredit'  => 'required|integer|min:0',
            'address'    => 'nullable|string|max:255',
        ]);

        if (!$agentDetails || $agentDetails->canregistersubagent == false || $agentDetails->status === 'deactivated') {
            return back()->withErrors(['subcredit' => 'You do not have permission to register sub agents.']);
        }

        if (!$poolMode) {
            // Individual mode: credits must be available on the parent
            $availablecredits = $agentDetails->noallocated - $agentDetails->noused;
            if ($availablecredits < $validatedData['subcredit']) {
                return back()->withErrors(['subcredit' => 'Insufficient credits available for allocation.']);
            }
        }

        $newSubAgent = User::create([
            'name'      => $validatedData['firstname'] . ' ' . $validatedData['lastname'],
            'telno'     => $validatedData['phone'],
            'adress'    => $validatedData['address'] ?? '',
            'firstname' => $validatedData['firstname'],
            'lastname'  => $validatedData['lastname'],
            'email'     => $validatedData['email'],
            'password'  => $validatedData['password'],
            'role'      => 'subagent',
            'parentid'  => $agent->id,
        ]);

        agentsdetailsModel::create([
            'uid'                => $newSubAgent->id,
            'noallocated'        => 0,
            'noused'             => 0,
            'allowcredit'        => true,
            'status'             => 'active',
            'puid'               => $agent->id,
            'issubagent'         => true,
            // Individual-mode fields
            'subcreditassigned'  => $poolMode ? 0 : $validatedData['subcredit'],
            'subcreditused'      => 0,
            // Pool-mode cap fields
            'pool_cap'           => $poolMode ? $validatedData['subcredit'] : 0,
            'pool_cap_used'      => 0,
        ]);

        if (!$poolMode && $validatedData['subcredit'] > 0) {
            // Reserve the allocated credits in the parent's noused
            $agentDetails->noused += $validatedData['subcredit'];
            $agentDetails->save();
        }

        return redirect()->route('list_sub_agents')->with('success', 'Sub agent registered successfully.');
    }

    // FUNCTION TO UPDATE SUB AGENT CREDITS BY AGENT
    public function subAgentCreditAdd(Request $request)
    {
        $validatedData = $request->validate([
            'subagent_id' => 'required|exists:users,id',
            'credits'     => 'required|integer|min:1',
        ]);

        $subAgent = User::where('id', $validatedData['subagent_id'])->first();
        if (!$subAgent || $subAgent->parentid != Auth::id()) {
            return back()->withErrors(['subagent_id' => 'Invalid sub agent selected.']);
        }

        $agentDetails    = agentsdetailsModel::where('uid', Auth::id())->first();
        $subAgentDetails = agentsdetailsModel::where('uid', $subAgent->id)->first();

        if (!$agentDetails) {
            return back()->withErrors(['credits' => 'Agent record not found.']);
        }
        if (!$subAgentDetails) {
            return back()->withErrors(['subagent_id' => 'Sub-agent credit record not found.']);
        }

        if ($agentDetails->pool_enabled) {
            // Pool mode: increase the subagent's personal cap only (no parent credit cost)
            $subAgentDetails->pool_cap += $validatedData['credits'];
            $subAgentDetails->save();
        } else {
            // Individual mode: reserve credits from parent's available balance
            $availableCredits = $agentDetails->noallocated - $agentDetails->noused;
            if ($availableCredits < $validatedData['credits']) {
                return back()->withErrors(['credits' => 'Insufficient credits available for allocation.']);
            }
            $subAgentDetails->subcreditassigned += $validatedData['credits'];
            $subAgentDetails->save();
            $agentDetails->noused += $validatedData['credits'];
            $agentDetails->save();
        }

        return back()->with('success', 'Credits updated successfully.');
    }

    public function subAgentCreditRemove(Request $request)
    {
        $validatedData = $request->validate([
            'subagent_id' => 'required|exists:users,id',
            'credits'     => 'required|integer|min:1',
        ]);

        $subAgent = User::where('id', $validatedData['subagent_id'])->first();
        if (!$subAgent || $subAgent->parentid != Auth::id()) {
            return back()->withErrors(['subagent_id' => 'Invalid sub agent selected.']);
        }

        $agentDetails    = agentsdetailsModel::where('uid', Auth::id())->first();
        $subAgentDetails = agentsdetailsModel::where('uid', $subAgent->id)->first();

        if (!$agentDetails) {
            return back()->withErrors(['credits' => 'Agent record not found.']);
        }
        if (!$subAgentDetails) {
            return back()->withErrors(['subagent_id' => 'Sub-agent credit record not found.']);
        }

        if ($agentDetails->pool_enabled) {
            // Pool mode: reduce the subagent's cap, but not below what they've already used
            $unusedCap = $subAgentDetails->pool_cap - $subAgentDetails->pool_cap_used;
            if ($unusedCap < $validatedData['credits']) {
                return back()->withErrors(['credits' => 'Cannot remove more than the subagent\'s unused cap.']);
            }
            $subAgentDetails->pool_cap -= $validatedData['credits'];
            $subAgentDetails->save();
        } else {
            // Individual mode: return credits to parent's available balance
            $unusedCredits = $subAgentDetails->subcreditassigned - $subAgentDetails->subcreditused;
            if ($unusedCredits < $validatedData['credits']) {
                return back()->withErrors(['credits' => 'Insufficient unused credits available for removal.']);
            }
            $subAgentDetails->subcreditassigned -= $validatedData['credits'];
            $subAgentDetails->save();
            $agentDetails->noused -= $validatedData['credits'];
            $agentDetails->save();
        }

        return back()->with('success', 'Credits updated successfully.');
    }

    // ── Pool Management ────────────────────────────────────────────────────

    /**
     * Enable, resize, or disable the shared credit pool for the logged-in agent.
     *
     * POST fields:
     *   action      — 'enable' | 'resize' | 'disable'
     *   pool_size   — required for 'enable' and 'resize'
     */
    public function poolUpdate(Request $request)
    {
        $request->validate([
            'action'    => 'required|in:enable,resize,disable',
            'pool_size' => 'required_if:action,enable,resize|integer|min:0',
        ]);

        $agentDetails = agentsdetailsModel::where('uid', Auth::id())->first();
        if (!$agentDetails) {
            return back()->withErrors(['action' => 'Agent record not found.']);
        }

        $action = $request->action;

        if ($action === 'enable') {
            if ($agentDetails->pool_enabled) {
                return back()->withErrors(['pool_size' => 'Pool is already enabled. Use resize to change the size.']);
            }
            $newSize          = (int) $request->pool_size;
            $availableCredits = $agentDetails->noallocated - $agentDetails->noused;
            if ($newSize > $availableCredits) {
                return back()->withErrors(['pool_size' => "Insufficient credits. You have {$availableCredits} available."]);
            }
            $agentDetails->pool_enabled = true;
            $agentDetails->pool_size    = $newSize;
            $agentDetails->pool_used    = 0;
            $agentDetails->noused      += $newSize;
            $agentDetails->save();
            return back()->with('success', "Credit pool enabled with {$newSize} credits.");
        }

        if ($action === 'resize') {
            if (!$agentDetails->pool_enabled) {
                return back()->withErrors(['pool_size' => 'Pool is not enabled.']);
            }
            $newSize  = (int) $request->pool_size;
            $oldSize  = $agentDetails->pool_size;
            $poolUsed = $agentDetails->pool_used;

            if ($newSize < $poolUsed) {
                return back()->withErrors(['pool_size' => "Cannot shrink pool below already-consumed credits ({$poolUsed})."]);
            }

            $delta = $newSize - $oldSize;

            if ($delta > 0) {
                // Growing: check parent has enough free credits
                $availableCredits = $agentDetails->noallocated - $agentDetails->noused;
                if ($delta > $availableCredits) {
                    return back()->withErrors(['pool_size' => "Insufficient credits. You have {$availableCredits} available."]);
                }
                $agentDetails->noused += $delta;
            } else {
                // Shrinking: release unused credits back
                $agentDetails->noused += $delta; // delta is negative
            }

            $agentDetails->pool_size = $newSize;
            $agentDetails->save();
            return back()->with('success', "Credit pool resized to {$newSize}.");
        }

        if ($action === 'disable') {
            if (!$agentDetails->pool_enabled) {
                return back()->withErrors(['action' => 'Pool is not enabled.']);
            }
            // Release unused pool credits back to the agent's free balance
            $unused                   = $agentDetails->pool_size - $agentDetails->pool_used;
            $agentDetails->noused     = max(0, $agentDetails->noused - $unused);
            $agentDetails->pool_enabled = false;
            $agentDetails->pool_size  = 0;
            $agentDetails->pool_used  = 0;
            $agentDetails->save();
            return back()->with('success', 'Credit pool disabled. Unused credits returned to your balance.');
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
    public function show(agentsdetailsModel $agentsdetailsModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(agentsdetailsModel $agentsdetailsModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, agentsdetailsModel $agentsdetailsModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(agentsdetailsModel $agentsdetailsModel)
    {
        //
    }
}
