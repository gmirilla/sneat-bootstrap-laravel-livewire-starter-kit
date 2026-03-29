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
        $agentslist = agentsdetailsModel::all();

        //filter based on role
        switch ($user->role) {
            case 'agent':
                # RETRIEVE ALL POLICIES CREATED BY THIS AGENT
                $policies = policy::where('agent_id', $user->id)->orderBy('updated_at', 'desc')->get();

                break;

            case 'subagent':
                # RETRIEVE ALL POLICIES CREATED BY THISSUBAGENT
                $policies = policy::where('agent_id', $user->id)->orderBy('updated_at', 'desc')->get();

                break;
            case 'admin':
                # Retreieve all policies
                $policies = policy::all();

                break;
            case 'superadmin':
                # Retreieve all policies
                $policies = policy::all();

                break;
            case 'user':
                # Retrieve policies created by and for this user this user
                $policies = policy::where('insured_id', $user->id)->get();
                break;

            default:
                # code...
                break;
        }
        $products = policy::select('producttype')->distinct()->pluck('producttype');

        return view('policy.policylist', compact('policies', 'products', 'user', 'agentslist'));
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
                #TO DO Get How Much Credit the Agent has and pay using assinged credit
                // Get Current Agency Credit
                // New logic to handle Sub Agents  with their own credits
                $agent = agentsdetailsModel::where('uid', $user->id)->first();
                if ($user->role == 'agent') {
                    $creditleft = $agent->noallocated - $agent->noused;
                    #To Get individual AUTH TOKEN
                    #1.  Check if the Agent has an Access Token From Elite
                    $token = $agent->auth_token;
                } elseif ($user->role == 'subagent') {
                    $subagentdetails = agentsdetailsModel::where('uid', $user->id)->first();

                    $creditleft = $subagentdetails->subcreditassigned - $subagentdetails->subcreditused;
                    //to get Auth token from Parent
                    $parentagent = agentsdetailsModel::where('uid', $subagentdetails->puid)->first();
                    $token = $parentagent->auth_token;
                }

                // Secondary check for Agency credit 
                if ($creditleft <= 0) {
                    $message = "You do not have sufficient Credits to make this purchase";
                    $error = 'error';
                    $errorcode = '402-004';
                    $message;
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
            #Get Agent Credit Balance and change to reflect success;
            if ($request->has('agencycredit')) {
                $agent->noused = $agent->noused + 1;
                $agent->save();
            }
            if ($user->role == 'subagent') {

                $subagentdetails->subcreditused = $subagentdetails->subcreditused + 1;
                $subagentdetails->noused = $subagentdetails->noused - 1;
                $subagentdetails->save();
            }
            $policy->save();

            #TO DO Upload policy to NIIP

            #Prepare Third Party Motor Policy API Data for NIIP

            $niipdata =
                [
                    "APIKey" => config('variables.NIIP_API_KEY'),
                    "Purpose" => $policy->niipvehicleuse,
                    "VehicleColor" => $policyrisk->vechiclecolorid,
                    "VehicleMake" => $policyrisk->getvmakeid(),
                    "VehicleModel" => $policyrisk->getvmodelid(),
                    "EngineCap" => 3, // TO DO Get Engine Capacity
                    "State" => $policy->stateid,
                    "LGA" => $policy->lgaid,
                    "RegNo" => $policyrisk->regno,
                    "ChassisNo" => $policyrisk->chassisno,
                    "EngineNo" => $policyrisk->engineno,
                    "PolicyHolderFirstName" => $policy->firstname,
                    "PolicyHolderLastName" => $policy->lastname,
                    "PolicyHolderMiddleName" => ' ',
                    "PolicyHolderMobileNo" => $policy->telno,
                    "PolicyHolderEmail" => $policy->email,
                    "PolicyHolderNIN" => '  ',
                    "IssueDate" => date('Y-m-d', strtotime($policy->start_date)),
                    "PolicyHolderAddress" => str_replace(' ', '', $policy->getaddress()),
                    "PolicyNumber" => $policy->policyno

                ];
            #encode NIIP Data to JSON

            PostNIIPDataSlow::dispatch($niipdata); // Non-blocking
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
        // Test asynchronous job dispatching
        $policy = policy::where('policyno', $request->policyno)->first();
        $policyrisk = $policy->getrisk();

        $niipdata = [
            "APIKey" => config('variables.NIIP_API_KEY'),
            "Purpose" => $policy->niipvehicleuse,
            "VehicleColor" => $policyrisk->vechiclecolorid,
            "VehicleMake" => $policyrisk->getvmakeid(),
            "VehicleModel" => $policyrisk->getvmodelid(),
            "EngineCap" => 3, // TO DO Get Engine Capacity
            "State" => $policy->stateid,
            "LGA" => $policy->lgaid,
            "RegNo" => $policyrisk->regno,
            "ChassisNo" => $policyrisk->chassisno,
            "EngineNo" => $policyrisk->engineno,
            "PolicyHolderFirstName" => $policy->firstname,
            "PolicyHolderLastName" => $policy->lastname,
            "PolicyHolderMiddleName" => ' ',
            "PolicyHolderMobileNo" => $policy->telno,
            "PolicyHolderEmail" => $policy->email,
            "PolicyHolderNIN" => '  ',
            "IssueDate" => date('Y-m-d', strtotime($policy->start_date)),
            "PolicyHolderAddress" => str_replace(' ', '', $policy->getaddress()),
            "PolicyNumber" => $policy->policyno
        ];

        //PostNIIPDataSlow::dispatch($niipdata); // Non-blocking
        // echo "NIIP data dispatched successfully.";

        // echo json_encode($niipdata);
        PostNIIPDataSlow::dispatch($niipdata);

        $id = $policy->id;
        $retrymessage = "NIIP data dispatched.";

        return redirect()->route('view_policy', compact('retrymessage', 'id'));
    }

    public function filterreport(Request $request)
    {
        Auth::check();
        $user = Auth::user();
        $agentslist = agentsdetailsModel::all();
        $query = Policy::query();
        $searchParams = $request->only(['policytype', 'status', 'datefrom', 'dateto', 'agentcode']);

        switch ($user->role) {
            case in_array($user->role, ['agent', 'subagent']):

                # code...

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
                }

                $policies = $query->get();
                $products = policy::select('producttype')->distinct()->pluck('producttype');

                break;
                            # code...
            case in_array($user->role, ['admin', 'superadmin']):


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
                }

                $policies = $query->get();
                $products = policy::select('producttype')->distinct()->pluck('producttype');

            default:

                break;
        }






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
}
