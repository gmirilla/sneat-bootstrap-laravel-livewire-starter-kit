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

        //filter based on role
        switch ($user->role) {
            case 'agent':
                # RETRIEVE ALL POLICIES CREATED BY THIS AGENT
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

        return view('policy.policylist', compact('policies', 'products'));
    }

    /**
     * Begin the Process of Purchasing a  resource.
     */
    public function buypolicy(Request $request)
    {
        //

        switch ($request) {
            case ($request->has('btnprivatemotor')):
                # began the purchase of a private motor policy
                $producttype = 'Private Motor Third Party';
                $contribution = 15000;
                $usekey = 'private';
                $insurancetype = 'Private';
                $vehicleuse = "car";
                $niipusecode = 3; // Private Motor
                break;

            case ($request->has('btncommercialmotor')):
                # began the purchase of a Commercial  motor policy
                $producttype = 'Commercial Motor Third Party';
                $contribution = 20000;
                $usekey = 'commercial';
                $insurancetype = 'Commercial';
                $vehicleuse = "car";
                $niipusecode = 8; // Commercial Motor
                break;
            case ($request->has('btnmotorcycle')):
                # began the purchase of a Motorcycle policy
                $producttype = 'Motorcycle Third Party';
                $contribution = 5000;
                $usekey = 'commercial';
                $insurancetype = 'Commercial';
                $vehicleuse = "motorcycle";
                $niipusecode = 4;  // Motorcycle
                break;
            default:
                # To Do  create a default 
                return back()->with('Error', 'Product not Configured imported successfully.');
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
     * USER HAS SUBMITTED A MOTOR POLICY FOR PURCHASE.
     */
    public function submitmpolicy(Request $request)
    {


        //$validatedata=$request->validate()

        $request->validate(
            ['chassisno' => ['required', 'regex:/^[^IO]*$/']],
            [
                'chassisno.regex' => 'The chassis number must not contain the letters "I" or "O".'
            ],
            ['niipusecode' => 'required|integer'],
            ['address' => 'required|string|max:250'],
            ['lgas' => 'required|integer'],
            ['state' => 'required|integer'],
            ['vehicletype' => 'required|string|max:50'],
            ['producttype' => 'required|string|max:100'],
            ['contribution' => 'required|numeric|min:0'],
            ['engineno' => 'required|string|max:50'],
            ['regno' => 'required|string|max:20'],
            ['vehiclemake' => 'required|integer'],
            ['vmodel' => 'required|integer'],
            ['yearofmake' => 'required|integer|min:1900|max:' . date('Y')],
            ['vehiclecolor' => 'required|integer'],
            ['fname' => 'required|string|max:100'],
            ['lname' => 'required|string|max:100'],
            ['phone' => 'required|string|max:15'],
            ['email' => 'required|email|max:150'],
            ['dob' => 'required|date'],
        );
        //validate chassis number to exclude I and O




        /**Check the User Authentication and Roles. 
         * If a new direct User Create a user profile
         * If an User is an Agent Create a User Profile for the Insured
         *  
         * 
         */

        Auth::check();
        $user = Auth::user();

        $fullname = $request->fname . "  " . $request->lname;
        switch ($user->role) {
            case 'admin':
                # code...
                break;
            case 'superadmin':
                # code...
                break;
            case 'agent':
                # If the User is registered as an agent first create new user account if phone number is unique

                $insured = User::where('telno', $request->phone)->first();
                if (empty($insured)) {


                    $genpassword = 'Password';

                    # create new insured user profile
                    $insured = new User();
                    $insured->firstname = $request->fname;
                    $insured->lastname = $request->lname;
                    $insured->name = $fullname;
                    if (User::where('email', $request->email)->exists()) {

                        # email already exists replace email with phone number
                        $insured->email = $request->phone . "@noemail.com";
                    } else {
                        $insured->email = $request->email;
                    }
                    $insured->gender = $request->gender;
                    $insured->dob = $request->dob;
                    $insured->telno = $request->phone;
                    $insured->state = $request->state;
                    $insured->address = $request->address;
                    $insured->stateid = $request->state;
                    $insured->lgaid = $request->lgas;
                    $insured->password = Hash::make($genpassword);

                    $insured->save();
                } else {
                    # Map policy to existing user...

                }
                #Create Motor Policy 
                $start_date = date_create();
                $end_date = date_add(date_create(), date_interval_create_from_date_string("1 year"));

                #GET Vehicle Make and Model To BE USED with NIIP integration
                $vmake = vehicleMake::where('niipvmid', $request->vehiclemake)->first();
                $vmodel = vehicleModel::where('vmodelid', $request->vmodel)->first();

                #check if policy exists
                if ($request->has('policyid')) {
                    $policy = policy::where('id', $request->policyid)->first();
                } else {
                    $policy = new policy();
                }
                $policy->firstname = $request->fname;
                $policy->lastname = $request->lname;
                $policy->telno = $request->phone;
                $policy->email = $request->email;
                $policy->insured_id = $insured->id;
                $policy->producttype = $request->producttype;
                $policy->insured_name = $fullname;
                $policy->agent_id = $user->id;
                $policy->status = 'draft';
                $policy->start_date = date_format($start_date, 'Y/m/d');
                $policy->end_date = date_format($end_date, 'Y/m/d');
                $policy->create_uid = $user->id;
                $policy->update_uid = $user->id;
                $policy->usekey = $request->vehicletype;
                ##TO DO Create Method to Calc Agents Commission and Contribution
                $policy->contribution = $request->contribution;
                $policy->commission = 0;
                $policy->insurancetype = $request->insurancetype;
                $policy->vehicleuse = $request->vehicleuse;
                $policy->stateid = $request->state;
                $policy->lgaid = $request->lgas;
                $policy->niipvehicleuse = $request->niipusecode;

                $policy->save();

                #Create New Policy Risk Object
                $policyrisk = new policyrisk();
                #TO DO product ID
                $policyrisk->product_id = 1;
                $policyrisk->regno = $request->regno;
                $policyrisk->policyid = $policy->id;
                $policyrisk->engineno = $request->engineno;
                $policyrisk->chassisno = $request->chassisno;
                $policyrisk->vehiclemake = $vmake->vmake;
                $policyrisk->vehiclemodel = $vmodel->vmodelname;
                $policyrisk->yearofmake = $request->yearofmake;
                $policyrisk->vechiclecolorid = $request->vehiclecolor;
                $policyrisk->vehiclecolor = vehiclecolor::where('colorid', $request->vehiclecolor)->first()->color;

                if ($policy->producttype == 'Private Motor Third Party') {
                    # code...
                    $policy->vehicleuse = 'car';
                    $policy->insurancetype = 'Private';
                    $policyrisk->contribution = 15000;
                } else if ($policy->producttype == 'Commercial Motor Third Party') {
                    # code...
                    $policy->vehicleuse = 'car';
                    $policy->insurancetype = 'Commercial';
                    $policyrisk->contribution = 20000;
                } elseif ($policy->producttype == 'Motorcycle Third Party') {
                    # code...
                    $policy->vehicleuse = 'motorcycle';
                    $policy->insurancetype = 'Motorcycle';
                    $policyrisk->contribution = 5000;
                }

                $policyrisk->save();


                break;
            case 'direct':
                # code...
                break;

            default:
                # code...
                break;
        }

        // Prepare Paystack Data for Online processing if selected later
        $paystackcontroller = new PaystacktransactionController();
        $pdetails = new Request([
            'email' => $policy->email,
            'amount' => $policy->contribution,
            'policy_id' => $policy->id
        ]);
        $paystack = $paystackcontroller->create_paystack_transaction($pdetails);
        $accesscode = $paystack->getContent();
        // End Paystack initialization

        return view('policy.confirmpolicy', compact('policy', 'policyrisk', 'user', 'accesscode'));
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
                $agent = agentsdetailsModel::where('uid', $user->id)->first();
                $creditleft = $agent->noallocated - $agent->noused;
                #To Get individual AUTH TOKEN
                #1.  Check if the Agent has an Access Token From Elite
                #2.  IF no AUTH TOKEN USE default users TOKEN
                $token = $agent->auth_token;

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

        $query = Policy::query();
        $searchParams = $request->only(['policytype', 'status', 'datefrom', 'dateto']);


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

        $policies = $query->get();
        $products = policy::select('producttype')->distinct()->pluck('producttype');

        return view('policy.policylist', compact('policies', 'products', 'searchParams'));
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
        dd($policy);
        return redirect()->route('view_policy', compact('id'));
    }
}
