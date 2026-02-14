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
        //
        $agent = Auth::user();
        $subagents = User::where('parentid', $agent->id)->get();
        $pagentdetails = agentsdetailsModel::where('uid', $agent->id)->first();
        $availableCredits = $pagentdetails->noallocated - $pagentdetails->noused ;
        return view('subagents.subagentslist', compact('subagents', 'agent', 'availableCredits'));
    }

    //REGISTER NEW SUB AGENTS BY AGENT
    public function registerSubAgent(User $agent, Request $request)
    {
        //
        $validatedData = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'subcredit' => 'required|integer|min:0',
            'address' => 'nullable|string|max:255'
        ]);
        
        //Check if agent has enough credits to allocate to sub agent
        $availablecredits = $agent->getagentdetails()->noallocated - $agent->getagentdetails()->noused;
        if ($availablecredits < $validatedData['subcredit']) {
            return back()->withErrors(['subcredit' => 'Insufficient credits available for allocation.']);
        }
        
        //Check if agent is authorized to register sub agents and is active

        if ($agent->getagentdetails()->canregistersubagent==false or $agent->getagentdetails()->status=='deactivated')
            {
                return back()->withErrors(['subcredit' => 'You do not have permission to register sub agents.']);
            }
        $newSubAgent = User::create([
            'name' => $validatedData['firstname'] . ' ' . $validatedData['lastname'],
            'telno'=> $validatedData['phone'],
            'adress'=> $validatedData['address'] ?? '',
            'firstname' => $validatedData['firstname'], 'lastname' => $validatedData['lastname'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role' => 'subagent',
            'parentid' => $agent->id
        ]);

        agentsdetailsModel::create([
            'uid' => $newSubAgent->id,
            'noallocated' => 0,
            'noused' => 0,
            'allowcredit' => true,
            'status' => 'active',
            'puid' =>$agent->id,
            'issubagent' => true,
            'subcreditassigned' => $validatedData['subcredit'],
            'subcreditused' => 0
        ]);

        //Update Agent's Used Credits 
        $agentdetails = agentsdetailsModel::where('uid', $agent->id)->first();
        $agentdetails->noused += $validatedData['subcredit']; $agentdetails->save();
        $availableCredits = $agentdetails->noallocated - $agentdetails->noused ;

        $subagents = User::where('issubagent', true)->where('parentid', $agent->id)->get();
        return redirect()->route('list_sub_agents')->with('success', 'Sub agent registered successfully.');
    }

// FUNCTION TO UPDATE SUB AGENT CREDITS BY AGENT
    public function subAgentCreditAdd(Request $request)
    {
        //
       
        $validatedData = $request->validate([
            'subagent_id' => 'required|exists:users,id',
            'credits' => 'required|integer|min:1'
        ]);


        $subAgent = User::where('id', $validatedData['subagent_id'])->first();
        if (!$subAgent || $subAgent->parentid != Auth::id()) {
            return back()->withErrors(['subagent_id' => 'Invalid sub agent selected.']);
        }

        $agent = Auth::user();
        $agentDetails = agentsdetailsModel::where('uid', $agent->id)->first();
        $subAgentDetails = agentsdetailsModel::where('uid', $subAgent->id)->first();

        // Check if agent has enough credits to allocate to sub agent
        $availableCredits = $agentDetails->noallocated - $agentDetails->noused;
        if ($availableCredits < $validatedData['credits']) {
            return back()->withErrors(['credits' => 'Insufficient credits available for allocation.']);
        }

        // Update sub agent's allocated credits and agent's used credits
        $subAgentDetails->subcreditassigned += $validatedData['credits'];
        $subAgentDetails->save();

        $agentDetails->noused += $validatedData['credits'];
        $agentDetails->save();

        return back()->with('success', 'Credits updated successfully.');
    } 
    
    public function subAgentCreditRemove(Request $request){

     $validatedData = $request->validate([
            'subagent_id' => 'required|exists:users,id',
            'credits' => 'required|integer|min:1'
        ]);


        $subAgent = User::where('id', $validatedData['subagent_id'])->first();
        if (!$subAgent || $subAgent->parentid != Auth::id()) {
            return back()->withErrors(['subagent_id' => 'Invalid sub agent selected.']);
        }

        $agent = Auth::user();
        $agentDetails = agentsdetailsModel::where('uid', $agent->id)->first();
        $subAgentDetails = agentsdetailsModel::where('uid', $subAgent->id)->first();

        // Check if subagent has enough credits to allocate to remove
        $availableCredits = $subAgentDetails->subcreditassigned - $subAgentDetails->subcreditused;
        if ($availableCredits < $validatedData['credits']) {
            return back()->withErrors(['credits' => 'Insufficient unused credits available for removal.']);
        }

        // Update sub agent's allocated credits and agent's used credits
        $subAgentDetails->subcreditassigned -= $validatedData['credits'];
        $subAgentDetails->save();

        $agentDetails->noused -= $validatedData['credits'];
        $agentDetails->save();

        return back()->with('success', 'Credits updated successfully.');


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
