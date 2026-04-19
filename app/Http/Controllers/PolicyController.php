<?php

namespace App\Http\Controllers;

use App\Models\agentsdetailsModel;
use App\Models\policy;
use App\Models\policyrisk;
use App\Models\states;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Models\vehicleMake;
use Illuminate\Support\Facades\Http;
use App\Exceptions\InvalidUserActionException;
use App\Models\vehiclecolor;
use App\Models\vehicleModel;
use App\Models\paystacktransaction;
use App\Jobs\PostNIIPDataSlow; // Import the job class
use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\PaystacktransactionController; //import the paystack controller. Not ideal but works for now. To do convert to service class later.


class PolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        Auth::check();
        $user = Auth::user();

        // Scope agentslist to avoid loading every agent for the dropdown
        $agentslist = in_array($user->role, ['admin', 'superadmin'])
            ? agentsdetailsModel::where('issubagent', false)->get()
            : collect();

        switch ($user->role) {
            case 'agent':
            case 'subagent':
                $policies = policy::where('agent_id', $user->id)
                    ->orderBy('updated_at', 'desc')
                    ->paginate(50);
                break;

            case 'admin':
            case 'superadmin':
                $policies = policy::orderBy('updated_at', 'desc')->paginate(50);
                break;

            case 'user':
                $policies = policy::where('insured_id', $user->id)
                    ->orderBy('updated_at', 'desc')
                    ->paginate(50);
                break;

            default:
                $policies = collect()->paginate(50);
                break;
        }

        $products    = policy::select('producttype')->distinct()->pluck('producttype');
        $searchParams = [];

        return view('policy.policylist', compact('policies', 'products', 'user', 'agentslist', 'searchParams'));
    }

    /**
     * Begin the Process of Purchasing a  resource.
     */
    public function buypolicy(Request $request)
    {
        //
        $vmakes = vehicleMake::orderBy('vmake')->get();
        $states = states::all();
        $colors = vehiclecolor::all();


        switch ($request) {
            case ($request->has('btnprivatemotor')):
                # begin the purchase of a private motor policy
                $producttype = 'Private Motor Third Party';
                $contribution = 15000;
                $usekey = 'private';
                $insurancetype = 'Private';
                $vehicleuse = "car";
                $niipusecode = 3; // Private Motor
                break;

            case ($request->has('btncommercialmotor')):
                # begin the purchase of a Commercial  motor policy
                $producttype = 'Commercial Motor Third Party';
                $contribution = 20000;
                $usekey = 'commercial';
                $insurancetype = 'Commercial';
                $vehicleuse = "car";
                $niipusecode = 8; // Commercial Motor
                break;
            case ($request->has('btnmotorcycle')):
                # begin the purchase of a Motorcycle policy
                $producttype = 'Motorcycle Third Party';
                $contribution = 5000;
                $usekey = 'commercial';
                $insurancetype = 'Commercial';
                $vehicleuse = "motorcycle";
                $niipusecode = 4;  // Motorcycle
                break;

            case ($request->has('btnsipp')):
                # begin the purchase of a SIP policy
                $producttype = 'Salam Investment Plan';
                $contribution = 0;
                $usekey = 'sip5000';
                $insurancetype = 'sip';
                $vehicleuse = "motorcycle"; //placeholder to be corrected on bitlect updates thier API
                $niipusecode = 'NA';  // SIP  not  applicable
                return view('policy.SIP.newpolicy', compact(
                    'vmakes',
                    'producttype',
                    'contribution',
                    'usekey',
                    'insurancetype',
                    'vehicleuse',
                    'states',
                    'colors',
                    'niipusecode'
                ));
                break;

                case ($request->has('btnoccupier')):
                # begin the purchase of a SIP policy
                $producttype = 'Occupier Liability Policy';
                $contribution = 0;
                $usekey = 'olp';
                $insurancetype = 'olp';
                $vehicleuse = "motorcycle"; //placeholder to be corrected on bitlect updates thier API
                $niipusecode = 'NA';  // SIP  not  applicable
                return view('policy.SIP.newpolicy', compact(
                    'vmakes',
                    'producttype',
                    'contribution',
                    'usekey',
                    'insurancetype',
                    'vehicleuse',
                    'states',
                    'colors',
                    'niipusecode'
                ));
                break;

            default:
                # To Do  create a default 
                return back()->with('Error', 'Product not Configured or imported successfully.');
                break;
        }
        $vmakes = vehicleMake::orderBy('vmake')->get();
        $states = states::all();
        $colors = vehiclecolor::all();


        return view('policy.newpolicy', compact('vmakes', 'producttype', 'contribution', 'usekey', 'insurancetype', 'vehicleuse', 'states', 'colors', 'niipusecode'));
    }

    /**
     * Begin the Process of Purchasing a  policy.
     */
    public function newpolicy()
    {
        //
        $vmakes = vehicleMake::orderBy('vmakes')->get();

        return view('policy.newpolicy', compact('vmakes'));
    }


    /**
     * USER HAS SUBMITTED A POLICY FOR PURCHASE.
     */
    public function submitmpolicy(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | 1. VALIDATION BASED ON PRODUCT TYPE
    |--------------------------------------------------------------------------
    */

        if ($request->producttype == 'Salam Investment Plan') {

            $request->validate([
                'contribution' => 'required|numeric|min:5000',
                'frequency'    => 'required|string',
                'fname'        => 'required|string|max:100',
                'lname'        => 'required|string|max:100',
                'phone'        => 'required|string|max:15',
                'email'        => 'required|email|max:150',
                'dob'          => 'required|date',
                
            ]);

            // Auto‑fill SIP‑specific fields
            $regno = str_replace(' ', '', $request->fname . $request->lname) . '-' . $request->producttype;

            $request->merge([
                'regno'      => $regno,
                'engineno'   => 'N/A',
                'chassisno'  => 'SIP',
                'yearofmake' => date('Y'),
            ]);
        } else {

            // MOTOR POLICY VALIDATION
            $request->validate([
                'chassisno'     => ['required', 'regex:/^[^IO]*$/'],
                'niipusecode'   => 'required|integer',
                'address'       => 'required|string|max:250',
                'lgas'          => 'required|integer',
                'state'         => 'required|integer',
                'vehicletype'   => 'required|string|max:50',
                'producttype'   => 'required|string|max:100',
                'contribution'  => 'required|numeric|min:0',
                'engineno'      => 'required|string|max:50',
                'regno'         => 'required|string|max:20',
                'vehiclemake'   => 'required|integer',
                'vmodel'        => 'required|integer',
                'yearofmake'    => 'required|integer|min:1900|max:' . date('Y'),
                'vehiclecolor'  => 'required|integer',
                'fname'         => 'required|string|max:100',
                'lname'         => 'required|string|max:100',
                'phone'         => 'required|string|max:15',
                'email'         => 'required|email|max:150',
                'dob'           => 'required|date',
                'start_date'    => 'required|date|after_or_equal:today',
            ], [
                'chassisno.regex' => 'The chassis number must not contain the letters "I" or "O".'
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | 2. USER ROLE HANDLING (AGENT CREATES INSURED)
    |--------------------------------------------------------------------------
    */

        $user = Auth::user();
        $fullname = $request->fname . " " . $request->lname;

        $insured = null;

        if ($user->role === 'agent' or $user->role === 'subagent') {

            // Check if insured exists
            $insured = User::where('telno', $request->phone)->first();

            if (!$insured) {

                $insured = new User();
                $insured->firstname = $request->fname;
                $insured->lastname  = $request->lname;
                $insured->name      = $fullname;
                $insured->email     = User::where('email', $request->email)->exists()
                    ? $request->phone . "@noemail.com"
                    : $request->email;

                $insured->gender    = $request->gender;
                $insured->dob       = $request->dob;
                $insured->telno     = $request->phone;
                $insured->state     = $request->state;
                $insured->address   = $request->address;
                $insured->stateid   = $request->state;
                $insured->lgaid     = $request->lgas;
                $insured->password  = Hash::make('Password');

                $insured->save();
            }
        } else {
            // Direct or admin users insure themselves
            $insured = $user;
        }


        /*
    |--------------------------------------------------------------------------
    | 3. CREATE OR UPDATE POLICY
    |--------------------------------------------------------------------------
    */

        $policy = $request->has('policyid')
            ? policy::find($request->policyid)
            : new policy();

        $start_date = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now();
        $end_date = $start_date->copy()->addYear()->subDay();


        $policy->firstname      = $request->fname;
        $policy->lastname       = $request->lname;
        $policy->telno          = $request->phone;
        $policy->email          = $request->email;
        $policy->insured_id     = $insured->id;
        $policy->producttype    = $request->producttype;
        $policy->insured_name   = $fullname;
        $policy->agent_id       = $user->id;
        $policy->status         = 'draft';
        $policy->start_date     = $start_date->format('Y/m/d');
        $policy->end_date       = $end_date->format('Y/m/d');
        $policy->create_uid     = $user->id;
        $policy->update_uid     = $user->id;
        $policy->usekey         = $request->vehicletype;
        $policy->contribution   = $request->contribution;
        $policy->commission     = 0;
        $policy->insurancetype  = $request->insurancetype ?? $request->producttype;
        $policy->vehicleuse     = $request->vehicleuse ?? 'n/a';
        $policy->stateid        = $request->state;
        $policy->lgaid          = $request->lgas;
        $policy->niipvehicleuse = $request->niipusecode;
        $policy->nin              = $request->nin ?? 'n/a';

        $policy->save();


        /*
    |--------------------------------------------------------------------------
    | 4. CREATE POLICY RISK
    |--------------------------------------------------------------------------
    */

        $vmake  = vehicleMake::where('niipvmid', $request->vehiclemake)->first();
        $vmodel = vehicleModel::where('vmodelid', $request->vmodel)->first();

        $policyrisk = new policyrisk();
        $policyrisk->product_id       = 1;
        $policyrisk->policyid         = $policy->id;
        $policyrisk->regno            = $request->regno;
        $policyrisk->engineno         = $request->engineno;
        $policyrisk->chassisno        = $request->chassisno;
        $policyrisk->yearofmake       = $request->yearofmake;
        $policyrisk->vechiclecolorid  = $request->vehiclecolor;

        if ($vmake) {
            $policyrisk->vehiclemake  = $vmake->vmake;
            $policyrisk->vehiclemodel = $vmodel->vmodelname;
            $policyrisk->vehiclecolor = vehiclecolor::where('colorid', $request->vehiclecolor)->first()->color;
        } else {
            $policyrisk->vehiclemake  = '0';
            $policyrisk->vehiclemodel = '0';
            $policyrisk->vehiclecolor = '0';
        }

        // Third‑party auto‑pricing
        if ($policy->producttype === 'Private Motor Third Party') {
            $policyrisk->contribution = 15000;
            $policy->vehicleuse = 'car';
            $policy->insurancetype = 'Private';
        } elseif ($policy->producttype === 'Commercial Motor Third Party') {
            $policyrisk->contribution = 20000;
            $policy->vehicleuse = 'car';
            $policy->insurancetype = 'Commercial';
        } elseif ($policy->producttype === 'Motorcycle Third Party') {
            $policyrisk->contribution = 5000;
            $policy->vehicleuse = 'motorcycle';
            $policy->insurancetype = 'Motorcycle';
        } else {
            // SIP or other custom products
            $policyrisk->contribution = $request->contribution;
            $policy->frequency = $request->frequency;
        }

        $policyrisk->save();
        $policy->save();


        /*
    |--------------------------------------------------------------------------
    | 5. PAYSTACK INITIALIZATION
    |--------------------------------------------------------------------------
    */

        try {
            $paystackcontroller = new PaystacktransactionController();
            $pdetails = new Request([
                'email'     => $policy->email,
                'amount'    => $policy->contribution,
                'policy_id' => $policy->id
            ]);

            $paystack   = $paystackcontroller->create_paystack_transaction($pdetails);
            $accesscode = $paystack->getContent();
        } catch (\Exception $e) {
            Log::error('Paystack Initialization Error: ' . $e->getMessage());
            $accesscode = null;
        }


        /*
    |--------------------------------------------------------------------------
    | 6. RETURN VIEW
    |--------------------------------------------------------------------------
    */

        if ($policy->producttype === 'Salam Investment Plan') {
            return view('policy.SIP.confirmpolicy', compact('policy', 'policyrisk', 'user', 'accesscode'));
        }

        return view('policy.confirmpolicy', compact('policy', 'policyrisk', 'user', 'accesscode'));


        //  not yet ready. Plan change the architecture to a more modern archiecture and refactor entire codebase
        // $id=$policy->id;
        // return redirect()->route('policy.confirm',compact('id'));

    }




    # INITIATE THE PAYSTACK PAYMENT PROCESSING
    public function init_paystack(policy $policy)
    {

        // Prepare Paystack Data for Online processing if selected later
        $paystackcontroller = new PaystacktransactionController();
        $pdetails = new Request([
            'email' => $policy->email,
            'amount' => $policy->contribution,
            'policy_id' => $policy->id
        ]);
        $paystack = $paystackcontroller->create_paystack_transaction($pdetails);
        $accesscode = $paystack->getContent();
        $policyid = $policy->id;
        $policyno = $policy->policyno;
        $contribution = $policy->contribution;
        $response = compact('accesscode', 'policyid', 'policyno', 'contribution');

        // End Paystack initialization

        return response()->json($response);
    }

    /**
     * the user  confirmed MOtor Policy details.
     */
    public function confirmmpolicy(Request $request)
    {
        //


        return view('policy.confirmpolicy');
    }

    public function paypolicy(Request $request)
    {
        //
        Auth::check();
        $user = Auth::user();
        $policy = policy::where('id', $request->policyid)->first();
        $policyrisk = policyrisk::where('policyid', $request->policyid)->first();
        $insured = User::where('id', $policy->insured_id)->first();
        $transaction = null;
        #Validation of mandatory Field with default values
        $gsm = $insured->telno;
        if (empty($gsm)) {
            # change Gsm to company number
            $gsm = '+234 806 565 7291';
        }


        #Handle Payment Method
        switch ($request) {
            case $request->has('agencycredit'):
                $agent = agentsdetailsModel::where('uid', $user->id)->first();
                if ($user->role == 'agent') {
                    $creditleft = $agent->noallocated - $agent->noused;
                    $token      = $agent->auth_token;
                } elseif ($user->role == 'subagent') {
                    $subagentdetails = agentsdetailsModel::where('uid', $user->id)->first();
                    $parentagent     = agentsdetailsModel::where('uid', $subagentdetails->puid)->first();
                    $token           = $parentagent->auth_token;

                    if ($parentagent->pool_enabled) {
                        $poolAvailable = $parentagent->pool_size - $parentagent->pool_used;
                        if ($subagentdetails->pool_cap > 0) {
                            // Personal cap within pool — honour the tighter of pool or cap
                            $capRemaining = $subagentdetails->pool_cap - $subagentdetails->pool_cap_used;
                            $creditleft   = min($poolAvailable, $capRemaining);
                        } else {
                            $creditleft = $poolAvailable;
                        }
                    } else {
                        $creditleft = $subagentdetails->subcreditassigned - $subagentdetails->subcreditused;
                    }
                }

                if ($creditleft <= 0) {
                    $message   = "You do not have sufficient Credits to make this purchase";
                    $error     = 'error';
                    $errorcode = '402-004';
                    return view('user_errors', compact('error', 'errorcode', 'message'));
                }
                break;

            case $request->has('paystack'):
                $token = env('PAYSTACK_ELITE_TOKEN'); //No agency token used for Direct payments 
                // Handle Paystack Pament Menthod
                //First initiate payment and get access code
                $paystackData = json_decode($request->paystack);
                // Paystack Payment Verification: check if payment was sucessful and the amount paid is correct
                $paystackcontroller = new PaystacktransactionController();
                $paystackresponse = $paystackcontroller->verify_payment($paystackData->reference);
                $paystackresponseData = json_decode($paystackresponse->getContent());
                $transaction = paystacktransaction::where('reference_code', $paystackData->reference)->where('policy_id', $policy->id)->first();
                $transaction->status = $paystackresponseData->data->status;
                $transaction->policyno = $policy->policyno;

                if ($policy->contribution == $paystackresponseData->data->amount / 100 && $paystackresponseData->data->status == 'success') {
                    #Payment is successful and matches the contribution amount
                    $transaction->status = 'Payment Sucessful';

                    # code...
                } else {
                    # code...
                    $transaction->status = 'Payment Error Contact Administrator';
                    $transaction->save();
                    $message = "There was an error processing your payment. Please contact support with Error Code 402-PS001";
                    $error = 'error';
                    $errorcode = '402-PS001';
                    $message;
                    return view('user_errors', compact('error', 'errorcode', 'message'));
                }

                $transaction->save();
                break;


            default:
                # code...
                break;
        }
        #push Data to Elite API for Processing
        #Use the ELite API and push data


        $policydata = [
            "fullName" => $policy->insured_name,
            "ContactAddress" => $insured->address,
            "mobileNumber" => $gsm,
            "Email" => $insured->email,
            "engineNumber" => $policyrisk->engineno,
            "chassisNumber" => $policyrisk->chassisno,
            "vehicleColor" => $policyrisk->vehiclecolor,
            "yearOfMake" => strval($policyrisk->yearofmake),
            "vehicleMake" => $policyrisk->vehiclemake,
            "registrationNumber" => $policyrisk->regno,
            "vehicleType" => $policy->vehicleuse,
            "engineCapacity" => "1.6L",
            "vehicleModel" => $policyrisk->vehiclemodel,
            "useOFVehicle" => 'n/a',
            "insuranceType" => $policy->usekey
        ];

        $policydatajSon = json_encode($policydata);



        if (empty($token)) {
            $accesstoken = config('variables.API_ELITE_TOKEN');
        } else {
            $accesstoken = $token;
        }


        $response = Http::withHeader('Auth-Token', $accesstoken)->withBody($policydatajSon)
            ->post(config('variables.API_ELITE_URL'));
        #handle response from elite check status for success/fail

        $policy->elite_msg = $response->body();


        // Decode JSON string into an associative array
        $data = json_decode($response->body(), true);

        if ($data['data']['status'] == 'success') {
            # code...

            $policy->elite_msg = $data['data']['status'] . $data['data']['message'];
            $policy->policyno = $data['data']['policy_number'];
            # Update transaction record with policy number
            if ($transaction) {
                $transaction->policyno = $policy->policyno;
                $transaction->save();
            }
            $policy->status = 'approved';
            $policy->save();
            // Deduct credit on Elite approval
            if ($request->has('agencycredit')) {
                if ($user->role == 'subagent') {
                    if ($parentagent->pool_enabled) {
                        // Pool mode — atomic deduction to prevent race conditions
                        DB::transaction(function () use ($parentagent, $subagentdetails) {
                            $parentagent->lockForUpdate()->increment('pool_used');
                            if ($subagentdetails->pool_cap > 0) {
                                $subagentdetails->increment('pool_cap_used');
                            }
                        });
                    } else {
                        // Individual allocation mode
                        $subagentdetails->subcreditused += 1;
                        $subagentdetails->noused        -= 1;
                        $subagentdetails->save();
                    }
                } else {
                    $agent->noused += 1;
                    $agent->save();
                }
            }
            $policy->save();

            // Upload policy to NIIP
            PostNIIPDataSlow::dispatch($this->buildNiipData($policy, $policyrisk)); // Non-blocking
        }
        // Handle failure response from Elite
        else {
            # code...


            $policy->elite_msg = $response->body();
            $policy->elite_msg = $data['data']['status'] . $data['data']['message'];
            $policy->policyno = '';
            $policy->status = 'failed';
            $policy->save();

            $errors = $policy->elite_msg;
            $id = $policy->id;

            return redirect()->route('view_policy', compact('errors', 'id'));
        }




        $policy->save();




        return redirect()->route('list_policy');
    }


    public function viewpolicy(Request $request)
    {
        //



        $producttype = 'TO DO';
        $policy = policy::where('id', $request->id)->first();
        $insured = User::where('id', $policy->insured_id)->first();
        $policyrisk = policyrisk::where('policyid', $policy->id)->first();
        $vmakes = vehicleMake::orderBy('vmake')->get();
        $states = states::all();
        $colors = vehiclecolor::all();
        $errors = $request->errors;
        $retrymessage = $request->retrymessage;

        //dd($policy);
        switch ($policy->producttype) {
            case 'Salam Investment Plan':
                # SIP Policy View
                return view('policy.SIP.viewpolicy', compact(
                    'policy',
                    'insured',
                    'policyrisk',
                    'producttype',
                    'states',
                    'errors',
                    'retrymessage'
                ));
                break;
            case 'Liability Policy':
                # Liability Policy View
                break;

            default:
                # Motor Policy View
                break;
        }

        return view('policy.viewpolicy', compact('policy', 'insured', 'policyrisk', 'vmakes', 'producttype', 'states', 'colors', 'retrymessage'));
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
    public function show(policy $policy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(policy $policy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, policy $policy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(policy $policy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function testapi()
    {
        //
        return view('api.apitest');
    }
    public function testasync()
    {
        // Test asynchronous job dispatching
        $niipdata = [
            //"APIKey" => config('variables.NIIP_API_KEY'),
            "Purpose" => 3, // Example purpose code
            "VehicleColor" => 1, // Example color ID
            "VehicleMake" => 1, // Example make ID
            "VehicleModel" => 1, // Example model ID
            "EngineCap" => 3,
            "State" => 1,
            "LGA" => 1,
            "RegNo" => 'ABC123',
            "ChassisNo" => 'CHASSIS123',
            "EngineNo" => 'ENGINE123',
            "PolicyHolderFirstName" => 'John',
            "PolicyHolderLastName" => 'Doe',
            "PolicyHolderMiddleName" => 'M',
            "PolicyHolderMobileNo" => '08012345678',
            "PolicyHolderEmail" => 'john.doe@example.com',
            "PolicyHolderNIN" => '12345678901',
            "IssueDate" => date('Y-m-d'),
            "PolicyHolderAddress" => '123 Main St, City, State',
            "PolicyNumber" => 'P/2025/KN-HQ/010401/016242'
        ];

        //PostNIIPDataSlow::dispatch($niipdata); // Non-blocking
        echo "NIIP data dispatched successfully.";

        PostNIIPDataSlow::dispatch($niipdata);
    }

    public function retryniip(Request $request)
    {
        $policy     = policy::where('policyno', $request->policyno)->first();
        $policyrisk = $policy->getrisk();

        $policy->niip_status = 'retry_queued';
        $policy->save();

        PostNIIPDataSlow::dispatch($this->buildNiipData($policy, $policyrisk));

        $id           = $policy->id;
        $retrymessage = 'NIIP data dispatched.';

        return redirect()->route('view_policy', compact('retrymessage', 'id'));
    }

    /**
     * Retry all failed NIIP submissions in bulk.
     * Restricted to admin / superadmin.
     */
    public function retryAllFailedNiip(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'superadmin'])) {
            return redirect()->route('niip_code_mgmt')
                ->with('error', 'Unauthorised.');
        }

        // Collect approved policies whose NIIP submission never succeeded.
        // DB-level filter catches the definitive failure patterns; the PHP
        // filter below catches JSON responses where isSuccess !== true.
        $candidates = policy::where('status', 'approved')
            ->whereNotNull('policyno')
            ->where('policyno', '!=', '')
            ->where(function ($q) {
                $q->whereNull('niip_status')
                  ->orWhere('niip_status', '')
                  ->orWhere('niip_status', 'Array')        // job bug: array cast to string
                  ->orWhere('niip_status', 'like', 'Error:%')
                  ->orWhere('niip_status', 'retry_queued'); // previously queued but job failed again
            })
            ->get();

        // Also check JSON responses that indicate failure
        $jsonFailed = policy::where('status', 'approved')
            ->whereNotNull('policyno')
            ->where('policyno', '!=', '')
            ->whereNotNull('niip_status')
            ->where('niip_status', 'not like', 'Error:%')
            ->where('niip_status', '!=', 'retry_queued')
            ->where('niip_status', '!=', 'Array')
            ->get()
            ->filter(fn($p) => $this->niipResponseFailed($p->niip_status));

        $failed = $candidates->merge($jsonFailed)->unique('id');

        if ($failed->isEmpty()) {
            return redirect()->route('niip_code_mgmt')
                ->with('success', 'No failed NIIP submissions found.');
        }

        $queued     = 0;
        $skipped    = 0;

        foreach ($failed as $policy) {
            $policyrisk = $policy->getrisk();

            if (!$policyrisk) {
                Log::warning("NIIP batch retry: no risk record for policy {$policy->policyno}");
                $skipped++;
                continue;
            }

            $policy->niip_status = 'retry_queued';
            $policy->save();

            PostNIIPDataSlow::dispatch($this->buildNiipData($policy, $policyrisk));
            $queued++;
        }

        $msg = "Queued {$queued} NIIP submission(s) for retry.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (missing risk record).";
        }

        Log::info("NIIP batch retry by {$user->name}: {$msg}");

        return redirect()->route('niip_code_mgmt')->with('success', $msg);
    }

    /**
     * Build the NIIP submission payload for a policy.
     * Single source of truth used by confirmmpolicy, retryniip, and retryAllFailedNiip.
     */
    private function buildNiipData(policy $policy, policyrisk $policyrisk): array
    {
        return [
            "APIKey"                  => config('variables.NIIP_API_KEY'),
            "Purpose"                 => $policy->niipvehicleuse,
            "VehicleColor"            => $policyrisk->vechiclecolorid,
            "VehicleMake"             => $policyrisk->getvmakeid(),
            "VehicleModel"            => $policyrisk->getvmodelid(),
            "EngineCap"               => 3, // TO DO: get actual engine capacity
            "State"                   => $policy->stateid,
            "LGA"                     => $policy->lgaid,
            "RegNo"                   => $policyrisk->regno,
            "ChassisNo"               => $policyrisk->chassisno,
            "EngineNo"                => $policyrisk->engineno,
            "PolicyHolderFirstName"   => $policy->firstname,
            "PolicyHolderLastName"    => $policy->lastname,
            "PolicyHolderMiddleName"  => ' ',
            "PolicyHolderMobileNo"    => $policy->telno,
            "PolicyHolderEmail"       => $policy->email,
            "PolicyHolderNIN"         => '  ',
            "IssueDate"               => date('Y-m-d', strtotime($policy->start_date)),
            "PolicyHolderAddress"     => str_replace(' ', '', $policy->getaddress()),
            "PolicyNumber"            => $policy->policyno,
        ];
    }

    /**
     * Returns true when a stored niip_status JSON string indicates a failed
     * API response (isSuccess !== true).
     */
    private function niipResponseFailed(?string $niipStatus): bool
    {
        if (empty($niipStatus)) {
            return true;
        }

        $decoded = json_decode($niipStatus, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Not valid JSON — treat as failed
            return true;
        }

        return !($decoded['isSuccess'] ?? false);
    }

    public function filterreport(Request $request)
    {
        Auth::check();
        $user = Auth::user();

        $agentslist   = in_array($user->role, ['admin', 'superadmin'])
            ? agentsdetailsModel::where('issubagent', false)->get()
            : collect();

        $searchParams = $request->only(['policytype', 'status', 'datefrom', 'dateto', 'agentcode']);
        $query        = policy::query();

        if (in_array($user->role, ['agent', 'subagent'])) {
            $query->where('agent_id', $user->id);
        }

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
        if ($request->filled('agentcode') && in_array($user->role, ['admin', 'superadmin'])) {
            $query->where('agent_id', $request->agentcode);
        }

        $policies = $query->orderBy('updated_at', 'desc')->paginate(50)->withQueryString();
        $products = policy::select('producttype')->distinct()->pluck('producttype');



        return view('policy.policylist', compact('policies', 'products', 'searchParams', 'user', 'agentslist'));
    }


 public function subagentfilterreport(Request $request)
    {
        Auth::check();
        $user = Auth::user();
        $agentslist = agentsdetailsModel::where('puid', $user->id)->get();
        $subagents = User::select('id')->where('parentid', $user->id)->distinct()->pluck('id');

        $query = Policy::query();
        $searchParams = $request->only(['policytype', 'status', 'datefrom', 'dateto', 'agentcode']);

                if ($request->filled('policytype')) {
                    $query->where('producttype', $request->policytype)->whereIn('agent_id', $subagents);
                }

                if ($request->filled('status')) {
                    $query->where('status', $request->status)->whereIn('agent_id', $subagents);
                }

                if ($request->filled('datefrom')) {
                    $query->whereDate('created_at', '>=', $request->datefrom)->whereIn('agent_id', $subagents);
                }

                if ($request->filled('dateto')) {
                    $query->whereDate('created_at', '<=', $request->dateto)->whereIn('agent_id', $subagents);
                }
                if ($request->filled('agentcode')) {
                    $query->where('agent_id', $request->agentcode)->whereIn('agent_id', $subagents);
                }

                $policies = $query->get();
                $products = policy::select('producttype')->distinct()->pluck('producttype');

        $totalsubagents=$subagents->count();
        $totalpolcount=$policies->count();


        return view('subagents.policylist', 
        compact('policies', 'products', 'searchParams', 'user', 'agentslist', 'totalpolcount','totalsubagents'));
    }

    /**
     * Display a listing of upcoming renewals.
     */
    public function renewalslist()
    {
        //

        Auth::check();
        $user = Auth::user();
        //filter based on role
        switch ($user->role) {
            case 'agent':
                # RETRIEVE ALL POLICIES CREATED BY THIS AGENT
                $policies = policy::where('agent_id', $user->id)->where('status', 'approved')
                    ->whereBetween('end_date', [now(), now()->addDays(30)])->orderBy('updated_at', 'desc')->get();

                break;
            case 'admin':
                # Retreieve all policies
                $policies = policy::whereBetween('end_date', [now(), now()->addDays(30)])->where('status', 'approved')
                    ->orderBy('updated_at', 'desc')->get();
                break;
            case 'superadmin':
                # Retreieve all policies
                $policies = policy::whereBetween('end_date', [now(), now()->addDays(30)])->where('status', 'approved')
                    ->orderBy('updated_at', 'desc')->get();
                break;
            case 'user':
                # Retrieve policies created by and for this user this user
                $policies = Policy::where('insured_id', $user->id)
                    ->whereBetween('end_date', [now(), now()->addDays(30)])->where('status', 'approved')
                    ->orderBy('updated_at', 'desc')
                    ->get();

                break;

            default:
                # code...
                break;
        }
        $products = policy::select('producttype')->distinct()->pluck('producttype');
        return view('policy.renewpolicylist', compact('policies', 'products'));
    }

    public function renewpolicy(Request $request)
    {
        //

        $policy = policy::where('id', $request->policy_id)->first();
        return redirect()->route('view_policy', compact('id'));
    }

    /**
     * Display a listing of the subagent resource.
     */
    public function listpolicySubagents(Request $request)
    {
        //

        Auth::check();
        $user = Auth::user();
        $agentslist = agentsdetailsModel::where('puid', $user->id)->orderBy('updated_at', 'desc')->get();

        //retrieve all products, policies created by users subagent

        $products = policy::select('producttype')->distinct()->pluck('producttype');
        $subagents = User::select('id')->where('parentid', $user->id)->distinct()->pluck('id');
        $policies = Policy::whereIn('agent_id', $subagents)
            ->orderBy('updated_at', 'desc')
            ->get();


        $searchParams = '';
        $totalsubagents=$subagents->count();
        $totalpolcount=$policies->count();

        return view('subagents.policylist', 
        compact('policies', 'products', 'user', 'agentslist', 'searchParams', 'totalsubagents', 'totalpolcount'));
    }

    /** Cancel a policy and refund credit used */
    public function cancelpolicy(Request $request){

    //dd($request);

    Auth::check();
    $user = Auth::user();
    if ($user->role == 'superadmin') {
        $policy = policy::where('id', $request->id)->first();

        
        if (!empty($policy) && $policy->status == 'approved') {
            $agent = agentsdetailsModel::where('uid', $policy->agent_id)->first();
            $creditLog = '';

            // Refund credit only if the policy was NOT paid by card
            if ($policy->getsuccesspayments()->count() == 0) {
                if ($agent && $agent->issubagent == false) {
                    // Regular agent
                    $agent->noused = max(0, $agent->noused - 1);
                    $agent->save();
                    $creditLog = ' | New credit count: ' . $agent->noused;
                } elseif ($agent) {
                    // Subagent — check if parent uses pool mode
                    $parentAgent = $agent->parentAgentDetails();
                    if ($parentAgent && $parentAgent->pool_enabled) {
                        $parentAgent->pool_used = max(0, $parentAgent->pool_used - 1);
                        $parentAgent->save();
                        if ($agent->pool_cap > 0) {
                            $agent->pool_cap_used = max(0, $agent->pool_cap_used - 1);
                            $agent->save();
                        }
                        $creditLog = ' | Pool refund: pool_used now ' . $parentAgent->pool_used;
                    } else {
                        $agent->subcreditused = max(0, $agent->subcreditused - 1);
                        $agent->save();
                        $creditLog = ' | New subcredit count: ' . $agent->subcreditused;
                    }
                }
            }

            $cancellreason=$request->cancellation_reason ?? 'No reason provided';

            $policy->status = 'cancelled';
            $policy->cancellation_reason ='Reason: '. $cancellreason . ' | Cancelled by ' . $user->name . $creditLog. ' | Cancellation Time: ' . now()->toDateTimeString();
            $policy->cancellation_date = now();
            $policy->cancellation_uid = $user->id;
            $policy->cancelled = true;
            $policy->save();
            // Refund credit if paid by agency credit
            /**
            if ($request->has('agencycredit')) {
                $agent = agentsdetailsModel::where('uid', $policy->agent_id)->first();
                if ($agent) {
                    $agent->noused = max(0, $agent->noused - 1); // Ensure noused doesn't go negative
                    $agent->save();
                }
            }
            **/

            return redirect()->route('list_policy')->with('success', 'Policy cancelled and credit refunded successfully.');
        } else {
            return redirect()->route('list_policy')->with('error', 'Policy not found.');
        }

    }
    else{
        return redirect()->route('list_policy')->with('error', 'Unauthorized Access');
    }

    }
}
