<?php

namespace App\Http\Controllers;

use App\Models\agentsdetailsModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentsdetailsModelController extends Controller
{
    public function index()
    {
        $agents = DB::table('users')
            ->leftJoin('agentsdetails_models', 'users.id', '=', 'agentsdetails_models.uid')
            ->select(
                'users.id as id',
                'name',
                'email',
                'allowcredit',
                'noallocated',
                'noused',
                'private_allocated',
                'private_used',
                'commercial_allocated',
                'commercial_used',
                'status',
                'auth_token'
            )->where('role', 'agent')->get();

        return view('usermgmgt.listagent', compact('agents'));
    }

    public function agentprofile(Request $request)
    {
        $user  = User::where('id', $request->uid)->first();
        $agent = agentsdetailsModel::where('uid', $request->uid)->first();

        return view('usermgmgt.agentdetails', compact('user', 'agent'));
    }

    public function subagentsprofile(User $sid)
    {
        $user  = $sid;
        $agent = agentsdetailsModel::where('uid', $sid->id)->first();

        return view('subagents.profile', compact('user', 'agent'));
    }

    // ── Admin: update agent credits ────────────────────────────────────────

    public function agentupdate(Request $request)
    {
        try {
            $agent = agentsdetailsModel::where('uid', $request->userid)->first();
            if (!$agent) {
                $agent      = new agentsdetailsModel();
                $agent->uid = $request->userid;
            }

            $agent->private_allocated    = (int) $request->private_allocated;
            $agent->commercial_allocated = (int) $request->commercial_allocated;
            // Keep total in sync
            $agent->noallocated = $agent->private_allocated + $agent->commercial_allocated;

            $agent->status              = $request->status;
            $agent->auth_token          = $request->authtoken;
            $agent->canregistersubagent = $request->has('agentregistersubagentchk');
            $agent->allowcredit         = $request->has('agentcreditchk');

            $agent->save();
        } catch (\Throwable $th) {
            //
        }

        return redirect()->route('list_agents');
    }

    // ── Sub-agent list ─────────────────────────────────────────────────────

    public function subagentsList(Request $request)
    {
        $agent        = Auth::user();
        $subagents    = User::where('parentid', $agent->id)->get();
        $agentDetails = agentsdetailsModel::where('uid', $agent->id)->first();

        $availablePrivate    = $agentDetails->availablePrivate();
        $availableCommercial = $agentDetails->availableCommercial();
        $availableCredits    = $availablePrivate + $availableCommercial;

        return view('subagents.subagentslist', compact(
            'subagents', 'agent', 'agentDetails',
            'availableCredits', 'availablePrivate', 'availableCommercial'
        ));
    }

    // ── Register new sub-agent ─────────────────────────────────────────────

    public function registerSubAgent(User $agent, Request $request)
    {
        $agentDetails = agentsdetailsModel::where('uid', $agent->id)->first();
        $poolMode     = $agentDetails && $agentDetails->pool_enabled;

        $validatedData = $request->validate([
            'firstname'            => 'required',
            'lastname'             => 'required',
            'phone'                => 'required',
            'email'                => 'required|email|unique:users,email',
            'password'             => 'required|min:6',
            'address'              => 'nullable|string|max:255',
            'subcredit_private'    => 'required|integer|min:0',
            'subcredit_commercial' => 'required|integer|min:0',
        ]);

        if (!$agentDetails || !$agentDetails->canregistersubagent || $agentDetails->status === 'deactivated') {
            return back()->withErrors(['subcredit_private' => 'You do not have permission to register sub agents.']);
        }

        $reqPrivate    = (int) $validatedData['subcredit_private'];
        $reqCommercial = (int) $validatedData['subcredit_commercial'];

        if (!$poolMode) {
            // Individual mode: both credit types must be available on parent
            if ($agentDetails->availablePrivate() < $reqPrivate) {
                return back()->withErrors(['subcredit_private' => 'Insufficient private credits available.']);
            }
            if ($agentDetails->availableCommercial() < $reqCommercial) {
                return back()->withErrors(['subcredit_commercial' => 'Insufficient commercial credits available.']);
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

        $subRecord = [
            'uid'         => $newSubAgent->id,
            'noallocated' => 0,
            'noused'      => 0,
            'allowcredit' => true,
            'status'      => 'active',
            'puid'        => $agent->id,
            'issubagent'  => true,
        ];

        if ($poolMode) {
            // Pool mode: caps only — no parent credit cost
            $subRecord['pool_cap_private']         = $reqPrivate;
            $subRecord['pool_cap_used_private']     = 0;
            $subRecord['pool_cap_commercial']       = $reqCommercial;
            $subRecord['pool_cap_used_commercial']  = 0;
            $subRecord['subcreditassigned_private']    = 0;
            $subRecord['subcreditassigned_commercial'] = 0;
        } else {
            // Individual mode: allocate from parent's typed balances
            $subRecord['subcreditassigned_private']    = $reqPrivate;
            $subRecord['subcreditassigned_commercial'] = $reqCommercial;
            $subRecord['subcreditused_private']        = 0;
            $subRecord['subcreditused_commercial']     = 0;
            $subRecord['subcreditassigned']            = $reqPrivate + $reqCommercial;
            $subRecord['subcreditused']                = 0;
        }

        agentsdetailsModel::create($subRecord);

        if (!$poolMode) {
            // Reserve allocated credits in parent's typed used counters
            if ($reqPrivate > 0) {
                $agentDetails->private_used += $reqPrivate;
            }
            if ($reqCommercial > 0) {
                $agentDetails->commercial_used += $reqCommercial;
            }
            $agentDetails->syncTotals();
            $agentDetails->save();
        }

        return redirect()->route('list_sub_agents')->with('success', 'Sub agent registered successfully.');
    }

    // ── Add credits / cap to sub-agent ────────────────────────────────────

    public function subAgentCreditAdd(Request $request)
    {
        $validatedData = $request->validate([
            'subagent_id'  => 'required|exists:users,id',
            'credit_type'  => 'required|in:private,commercial',
            'credits'      => 'required|integer|min:1',
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

        $type    = $validatedData['credit_type'];
        $credits = $validatedData['credits'];

        if ($agentDetails->pool_enabled) {
            // Pool mode: raise cap only — no parent credit cost
            if ($type === 'private') {
                $subAgentDetails->pool_cap_private += $credits;
            } else {
                $subAgentDetails->pool_cap_commercial += $credits;
            }
            $subAgentDetails->save();
        } else {
            // Individual mode: check parent availability for this type
            $available = $agentDetails->availableByType($type);
            if ($available < $credits) {
                return back()->withErrors(['credits' => "Insufficient {$type} credits available (have {$available})."]);
            }

            if ($type === 'private') {
                $subAgentDetails->subcreditassigned_private += $credits;
                $agentDetails->private_used                += $credits;
            } else {
                $subAgentDetails->subcreditassigned_commercial += $credits;
                $agentDetails->commercial_used                 += $credits;
            }
            $subAgentDetails->syncSubcreditTotals();
            $subAgentDetails->save();
            $agentDetails->syncTotals();
            $agentDetails->save();
        }

        return back()->with('success', ucfirst($type) . ' credits updated successfully.');
    }

    // ── Remove credits / cap from sub-agent ───────────────────────────────

    public function subAgentCreditRemove(Request $request)
    {
        $validatedData = $request->validate([
            'subagent_id'  => 'required|exists:users,id',
            'credit_type'  => 'required|in:private,commercial',
            'credits'      => 'required|integer|min:1',
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

        $type    = $validatedData['credit_type'];
        $credits = $validatedData['credits'];

        if ($agentDetails->pool_enabled) {
            // Pool mode: reduce cap, but not below used
            $cap     = $type === 'private' ? $subAgentDetails->pool_cap_private     : $subAgentDetails->pool_cap_commercial;
            $capUsed = $type === 'private' ? $subAgentDetails->pool_cap_used_private : $subAgentDetails->pool_cap_used_commercial;
            $unused  = $cap - $capUsed;
            if ($unused < $credits) {
                return back()->withErrors(['credits' => "Cannot remove more than the sub-agent's unused {$type} cap ({$unused})."]);
            }
            if ($type === 'private') {
                $subAgentDetails->pool_cap_private -= $credits;
            } else {
                $subAgentDetails->pool_cap_commercial -= $credits;
            }
            $subAgentDetails->save();
        } else {
            // Individual mode: only remove unused credits
            $assigned = $type === 'private' ? $subAgentDetails->subcreditassigned_private     : $subAgentDetails->subcreditassigned_commercial;
            $used     = $type === 'private' ? $subAgentDetails->subcreditused_private         : $subAgentDetails->subcreditused_commercial;
            $unused   = $assigned - $used;
            if ($unused < $credits) {
                return back()->withErrors(['credits' => "Insufficient unused {$type} credits available for removal ({$unused})."]);
            }

            if ($type === 'private') {
                $subAgentDetails->subcreditassigned_private -= $credits;
                $agentDetails->private_used                -= $credits;
            } else {
                $subAgentDetails->subcreditassigned_commercial -= $credits;
                $agentDetails->commercial_used                 -= $credits;
            }
            $subAgentDetails->syncSubcreditTotals();
            $subAgentDetails->save();
            $agentDetails->syncTotals();
            $agentDetails->save();
        }

        return back()->with('success', ucfirst($type) . ' credits updated successfully.');
    }

    // ── Pool Management ────────────────────────────────────────────────────

    /**
     * Enable, resize, or disable the shared credit pool.
     *
     * POST fields:
     *   action               — 'enable' | 'resize' | 'disable'
     *   pool_private_size    — credits to commit to the private pool
     *   pool_commercial_size — credits to commit to the commercial pool
     */
    public function poolUpdate(Request $request)
    {
        $request->validate([
            'action'               => 'required|in:enable,resize,disable',
            'pool_private_size'    => 'required_if:action,enable,resize|integer|min:0',
            'pool_commercial_size' => 'required_if:action,enable,resize|integer|min:0',
        ]);

        $agentDetails = agentsdetailsModel::where('uid', Auth::id())->first();
        if (!$agentDetails) {
            return back()->withErrors(['action' => 'Agent record not found.']);
        }

        $action = $request->action;

        if ($action === 'enable') {
            if ($agentDetails->pool_enabled) {
                return back()->withErrors(['action' => 'Pool is already enabled. Use resize to change sizes.']);
            }

            $newPrivate    = (int) $request->pool_private_size;
            $newCommercial = (int) $request->pool_commercial_size;

            if ($newPrivate > $agentDetails->availablePrivate()) {
                return back()->withErrors(['pool_private_size' => "Insufficient private credits (have {$agentDetails->availablePrivate()})."]);
            }
            if ($newCommercial > $agentDetails->availableCommercial()) {
                return back()->withErrors(['pool_commercial_size' => "Insufficient commercial credits (have {$agentDetails->availableCommercial()})."]);
            }

            $agentDetails->pool_enabled          = true;
            $agentDetails->pool_private_size     = $newPrivate;
            $agentDetails->pool_private_used     = 0;
            $agentDetails->pool_commercial_size  = $newCommercial;
            $agentDetails->pool_commercial_used  = 0;
            // Reserve pool credits in typed used counters
            $agentDetails->private_used    += $newPrivate;
            $agentDetails->commercial_used += $newCommercial;
            $agentDetails->syncTotals();
            $agentDetails->save();

            return back()->with('success', "Pool enabled — Private: {$newPrivate}, Commercial: {$newCommercial}.");
        }

        if ($action === 'resize') {
            if (!$agentDetails->pool_enabled) {
                return back()->withErrors(['action' => 'Pool is not enabled.']);
            }

            $newPrivate    = (int) $request->pool_private_size;
            $newCommercial = (int) $request->pool_commercial_size;

            if ($newPrivate < $agentDetails->pool_private_used) {
                return back()->withErrors(['pool_private_size' => "Cannot shrink below already-consumed private credits ({$agentDetails->pool_private_used})."]);
            }
            if ($newCommercial < $agentDetails->pool_commercial_used) {
                return back()->withErrors(['pool_commercial_size' => "Cannot shrink below already-consumed commercial credits ({$agentDetails->pool_commercial_used})."]);
            }

            $deltaPrivate    = $newPrivate    - $agentDetails->pool_private_size;
            $deltaCommercial = $newCommercial - $agentDetails->pool_commercial_size;

            if ($deltaPrivate > 0 && $deltaPrivate > $agentDetails->availablePrivate()) {
                return back()->withErrors(['pool_private_size' => "Insufficient private credits to grow pool (have {$agentDetails->availablePrivate()})."]);
            }
            if ($deltaCommercial > 0 && $deltaCommercial > $agentDetails->availableCommercial()) {
                return back()->withErrors(['pool_commercial_size' => "Insufficient commercial credits to grow pool (have {$agentDetails->availableCommercial()})."]);
            }

            $agentDetails->pool_private_size    = $newPrivate;
            $agentDetails->pool_commercial_size = $newCommercial;
            $agentDetails->private_used    += $deltaPrivate;    // negative delta = release
            $agentDetails->commercial_used += $deltaCommercial;
            $agentDetails->syncTotals();
            $agentDetails->save();

            return back()->with('success', "Pool resized — Private: {$newPrivate}, Commercial: {$newCommercial}.");
        }

        if ($action === 'disable') {
            if (!$agentDetails->pool_enabled) {
                return back()->withErrors(['action' => 'Pool is not enabled.']);
            }
            // Release unused pool credits back
            $unusedPrivate    = $agentDetails->pool_private_size    - $agentDetails->pool_private_used;
            $unusedCommercial = $agentDetails->pool_commercial_size - $agentDetails->pool_commercial_used;

            $agentDetails->private_used    = max(0, $agentDetails->private_used    - $unusedPrivate);
            $agentDetails->commercial_used = max(0, $agentDetails->commercial_used - $unusedCommercial);
            $agentDetails->pool_enabled          = false;
            $agentDetails->pool_private_size     = 0;
            $agentDetails->pool_private_used     = 0;
            $agentDetails->pool_commercial_size  = 0;
            $agentDetails->pool_commercial_used  = 0;
            $agentDetails->syncTotals();
            $agentDetails->save();

            return back()->with('success', 'Pool disabled. Unused credits returned to your balance.');
        }
    }

    public function create() {}
    public function store(Request $request) {}
    public function show(agentsdetailsModel $agentsdetailsModel) {}
    public function edit(agentsdetailsModel $agentsdetailsModel) {}
    public function update(Request $request, agentsdetailsModel $agentsdetailsModel) {}
    public function destroy(agentsdetailsModel $agentsdetailsModel) {}
}
