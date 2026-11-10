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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
                    ->with(['risk', 'agentUser'])
                    ->paginate(50);
                break;

            case 'admin':
            case 'superadmin':
                $policies = policy::orderBy('updated_at', 'desc')
                    ->with(['risk', 'agentUser'])
                    ->paginate(50);
                break;

            case 'user':
                $policies = policy::where('insured_id', $user->id)
                    ->orderBy('updated_at', 'desc')
                    ->with(['risk', 'agentUser'])
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


        switch (true) {
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
            // Match only end-customer accounts: an agent must never attach policies to staff/broker accounts by phone number.
            $insured = User::where('telno', $request->phone)->where('role', 'user')->first();

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
                $insured->password  = Hash::make(\Illuminate\Support\Str::random(40)); // unguessable; insured can claim the account via "forgot password"

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

        if ($request->filled('policyid')) {
            $policy = policy::findOrFail($request->policyid);
            // Only the owner (or an admin) may edit, and only before the policy is issued.
            $this->authorizePolicy('update', $policy);
        } else {
            $policy = new policy();
        }

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
        $this->authorizePolicy('update', $policy);


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
        $user = Auth::user();

        $request->validate(['policyid' => 'required|integer|exists:policies,id']);

        // Serialise issuance per policy so a double-click / replay cannot issue it twice.
        $lock = Cache::lock('pay-policy-' . $request->policyid, 120);
        if (!$lock->get()) {
            return $this->paymentError('This policy is already being processed. Please wait a moment and check the policy list.', '409-002');
        }

        $reservation = null;

        try {
            $policy = policy::findOrFail($request->policyid);

            // Ownership (creator, insured, parent agent or admin) AND still unissued (draft/failed).
            abort_unless(Gate::allows('view', $policy), 403);
            if (!Gate::allows('update', $policy)) {
                return $this->paymentError('This policy has already been issued or can no longer be paid for.', '409-001');
            }

            $policyrisk = policyrisk::where('policyid', $policy->id)->first();
            $insured    = User::find($policy->insured_id);
            if (!$policyrisk || !$insured) {
                return $this->paymentError('This policy is incomplete (missing risk or insured details).', '422-001');
            }

            $transaction = null;
            $token       = null;
            $creditType  = $this->creditType($policy->producttype);

            #Validation of mandatory Field with default values
            $gsm = $insured->telno;
            if (empty($gsm)) {
                # change Gsm to company number
                $gsm = '+234 806 565 7291';
            }

            #Handle Payment Method — exactly one valid method is required
            if ($request->has('agencycredit')) {
                $reservation = $this->reserveCredit($user, $creditType);

                if (!$reservation['ok']) {
                    $typeLabel = $creditType === 'commercial' ? 'Commercial' : 'Private';
                    return $this->paymentError("You do not have sufficient {$typeLabel} credits to make this purchase", '402-004');
                }
                $token = $reservation['token'];

            } elseif ($request->has('paystack')) {
                $token = config('variables.PAYSTACK_ELITE_TOKEN'); //No agency token used for Direct payments

                $paystackData = json_decode((string) $request->paystack);
                $reference    = is_object($paystackData) ? ($paystackData->reference ?? null) : null;
                if (!is_string($reference) || $reference === '') {
                    return $this->paymentError('Invalid payment reference.', '402-PS002');
                }

                // The reference must belong to a transaction we created for THIS policy.
                $transaction = paystacktransaction::where('reference_code', $reference)->where('policy_id', $policy->id)->first();
                if (!$transaction) {
                    return $this->paymentError('Payment reference not recognised for this policy.', '402-PS002');
                }

                // A reference is single-use: refuse if it is also recorded against any other policy.
                if (paystacktransaction::where('reference_code', $reference)->where('id', '!=', $transaction->id)->exists()) {
                    return $this->paymentError('This payment reference has already been used.', '402-PS003');
                }

                $paystackresponse     = (new PaystacktransactionController())->verify_payment($reference);
                $paystackresponseData = json_decode($paystackresponse->getContent());
                $paidData             = $paystackresponseData->data ?? null;

                // Compare integer kobo, never floats.
                $expectedKobo = (int) round($policy->contribution * 100);
                $verified     = $paidData
                    && ($paidData->status ?? null) === 'success'
                    && (int) ($paidData->amount ?? -1) === $expectedKobo
                    && (($paidData->currency ?? 'NGN') === 'NGN');

                if (!$verified) {
                    $transaction->status = 'Payment Error Contact Administrator';
                    $transaction->save();
                    return $this->paymentError('There was an error processing your payment. Please contact support with Error Code 402-PS001', '402-PS001');
                }

                $transaction->status = 'Payment Sucessful';
                $transaction->save();

            } else {
                return $this->paymentError('No payment method was selected.', '402-000');
            }

            #push Data to Elite API for Processing
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

            $accesstoken = empty($token) ? config('variables.API_ELITE_TOKEN') : $token;

            try {
                $response = Http::withHeader('Auth-Token', $accesstoken)
                    ->withBody($policydatajSon)
                    ->timeout(30)
                    ->retry(3, 3000, fn (\Exception $e) => $e instanceof \Illuminate\Http\Client\ConnectionException)
                    ->post(config('variables.API_ELITE_URL'));
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Elite API unreachable or timed out after 3 attempts — leave policy as draft, release the reserved credit
                Log::error('Elite API timeout after 3 attempts in paypolicy', [
                    'policy_id' => $policy->id,
                    'message'   => $e->getMessage(),
                ]);
                $this->releaseCredit($reservation);
                $reservation = null;

                $policy->elite_msg = 'Elite API timeout: ' . $e->getMessage();
                $policy->status    = 'draft';
                $policy->save();

                $errors = 'The insurance server did not respond in time. Your policy has NOT been issued. Please try again shortly.';
                $id     = $policy->id;
                return redirect()->route('view_policy', compact('errors', 'id'));
            }

            $policy->elite_msg = $response->body();

            // Decode JSON string into an associative array
            $data = json_decode($response->body(), true);

            if (($data['data']['status'] ?? '') == 'success') {

                $policy->elite_msg = $data['data']['status'] . $data['data']['message'];
                $policy->policyno  = $data['data']['policy_number'];

                if ($transaction) {
                    $transaction->policyno = $policy->policyno;
                    $transaction->save();
                }
                $policy->status = 'approved';
                $policy->save();

                // Credit was reserved before the Elite call; it is now permanently consumed.
                $reservation = null;

                // Upload policy to NIIP
                PostNIIPDataSlow::dispatch($this->buildNiipData($policy, $policyrisk)); // Non-blocking
            } else {
                // Elite returned a failure response — give the reserved credit back
                $this->releaseCredit($reservation);
                $reservation = null;

                $policy->elite_msg = ($data['data']['status'] ?? '') . ($data['data']['message'] ?? $response->body());
                $policy->policyno  = '';
                $policy->status    = 'failed';
                $policy->save();

                $errors = $policy->elite_msg;
                $id     = $policy->id;

                return redirect()->route('view_policy', compact('errors', 'id'));
            }

            return redirect()->route('list_policy');
        } catch (\Throwable $e) {
            // Any unexpected failure after a reservation must not leak the credit.
            $this->releaseCredit($reservation);
            throw $e;
        } finally {
            $lock->release();
        }
    }

    private function paymentError(string $message, string $errorcode)
    {
        $error = 'error';
        return view('user_errors', compact('error', 'errorcode', 'message'));
    }

    /**
     * Atomically check and reserve one credit of $creditType for $user.
     * Row locks make check + increment a single critical section.
     *
     * @return array{ok: bool, token?: ?string, undo?: callable}
     */
    private function reserveCredit(User $user, string $creditType): array
    {
        if (!in_array($user->role, ['agent', 'subagent'], true)) {
            return ['ok' => false];
        }

        return DB::transaction(function () use ($user, $creditType) {
            $own = agentsdetailsModel::where('uid', $user->id)->lockForUpdate()->first();

            if (!$own || !$own->allowcredit || $own->status === 'deactivated') {
                return ['ok' => false];
            }

            $isPrivate = $creditType === 'private';

            if ($user->role === 'agent') {
                if ($own->availableByType($creditType) <= 0) {
                    return ['ok' => false];
                }

                $col = $isPrivate ? 'private_used' : 'commercial_used';
                $own->$col += 1;
                $own->syncTotals();
                $own->save();

                return [
                    'ok'    => true,
                    'token' => $own->auth_token,
                    'undo'  => function () use ($own, $col) {
                        $row = agentsdetailsModel::where('id', $own->id)->lockForUpdate()->first();
                        $row->$col = max(0, $row->$col - 1);
                        $row->syncTotals();
                        $row->save();
                    },
                ];
            }

            // Sub-agent: draws on the parent's pool or on its own allocation.
            $parent = agentsdetailsModel::where('uid', $own->puid)->lockForUpdate()->first();
            if (!$parent || $parent->status === 'deactivated') {
                return ['ok' => false];
            }

            if ($parent->pool_enabled) {
                $poolCol = $isPrivate ? 'pool_private_used' : 'pool_commercial_used';
                $capCol  = $isPrivate ? 'pool_cap_used_private' : 'pool_cap_used_commercial';
                $cap     = $isPrivate ? $own->pool_cap_private : $own->pool_cap_commercial;
                $capUsed = $isPrivate ? $own->pool_cap_used_private : $own->pool_cap_used_commercial;
                $poolAvailable = $isPrivate
                    ? $parent->pool_private_size - $parent->pool_private_used
                    : $parent->pool_commercial_size - $parent->pool_commercial_used;

                $left = $cap > 0 ? min($poolAvailable, $cap - $capUsed) : $poolAvailable;
                if ($left <= 0) {
                    return ['ok' => false];
                }

                $parent->$poolCol += 1;
                $parent->save();
                if ($cap > 0) {
                    $own->$capCol += 1;
                    $own->save();
                }

                return [
                    'ok'    => true,
                    'token' => $parent->auth_token,
                    'undo'  => function () use ($parent, $own, $poolCol, $capCol, $cap) {
                        $p = agentsdetailsModel::where('id', $parent->id)->lockForUpdate()->first();
                        $p->$poolCol = max(0, $p->$poolCol - 1);
                        $p->save();
                        if ($cap > 0) {
                            $o = agentsdetailsModel::where('id', $own->id)->lockForUpdate()->first();
                            $o->$capCol = max(0, $o->$capCol - 1);
                            $o->save();
                        }
                    },
                ];
            }

            $assigned = $isPrivate ? $own->subcreditassigned_private : $own->subcreditassigned_commercial;
            $used     = $isPrivate ? $own->subcreditused_private : $own->subcreditused_commercial;
            if ($assigned - $used <= 0) {
                return ['ok' => false];
            }

            $usedCol = $isPrivate ? 'subcreditused_private' : 'subcreditused_commercial';
            $own->$usedCol += 1;
            $own->syncSubcreditTotals();
            $own->save();

            return [
                'ok'    => true,
                'token' => $parent->auth_token,
                'undo'  => function () use ($own, $usedCol) {
                    $row = agentsdetailsModel::where('id', $own->id)->lockForUpdate()->first();
                    $row->$usedCol = max(0, $row->$usedCol - 1);
                    $row->syncSubcreditTotals();
                    $row->save();
                },
            ];
        });
    }

    /** Give back a credit taken by reserveCredit() (no-op when nothing was reserved). */
    private function releaseCredit(?array $reservation): void
    {
        if ($reservation && ($reservation['ok'] ?? false) && isset($reservation['undo'])) {
            DB::transaction($reservation['undo']);
        }
    }


    public function viewpolicy(Request $request)
    {
        //



        $producttype = 'TO DO';
        $policy = policy::findOrFail($request->id);
        $this->authorizePolicy('view', $policy);
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
    public function retryniip(Request $request)
    {
        $policy     = policy::where('policyno', $request->policyno)->firstOrFail();
        $policyrisk = $policy->getrisk();
        abort_unless($policyrisk, 422, 'Policy has no risk record.');

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
    /**
     * Map a product type string to its credit category.
     * Only "Commercial Motor Third Party" is commercial; everything else is private.
     */
    private function creditType(string $producttype): string
    {
        return strtolower(trim($producttype)) === 'commercial motor third party' ? 'commercial' : 'private';
    }

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

    public function csvExport(Request $request)
    {
        $user     = Auth::user();
        $isAdmin  = in_array($user->role, ['admin', 'superadmin']);
        $query    = policy::query()->with(['risk', 'agentUser']);

        if (in_array($user->role, ['agent', 'subagent'])) {
            $query->where('agent_id', $user->id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('policyno', 'like', "%{$term}%")
                  ->orWhere('insured_name', 'like', "%{$term}%")
                  ->orWhereIn('id', function ($sub) use ($term) {
                      $sub->select('policyid')->from('policyrisks')
                          ->where('regno', 'like', "%{$term}%");
                  });
            });
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
        if ($request->filled('agentcode') && $isAdmin) {
            $query->where('agent_id', $request->agentcode);
        }

        $filename = 'policies-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query, $isAdmin) {
            $handle = fopen('php://output', 'w');

            $header = ['Policy No.', 'Policy Type', 'Reg No.', 'Insured Name', 'Contribution', 'Created At', 'Status'];
            if ($isAdmin) {
                array_push($header, 'Agent', 'Parent Agent');
            }
            fputcsv($handle, $header);

            $query->orderBy('updated_at', 'desc')->chunk(200, function ($policies) use ($handle, $isAdmin) {
                foreach ($policies as $policy) {
                    $row = [
                        $policy->policyno ?: 'Incomplete',
                        $policy->producttype,
                        $policy->risk?->regno ?? 'N/A',
                        $policy->insured_name,
                        number_format((float) $policy->contribution, 2),
                        $policy->created_at->format('d M Y H:i'),
                        $policy->status,
                    ];
                    if ($isAdmin) {
                        $row[] = $policy->getagentname();
                        $row[] = $policy->getparentagentname();
                    }
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-cache, must-revalidate',
        ]);
    }

    public function filterreport(Request $request)
    {
        Auth::check();
        $user = Auth::user();

        $agentslist   = in_array($user->role, ['admin', 'superadmin'])
            ? agentsdetailsModel::where('issubagent', false)->get()
            : collect();

        $searchParams = $request->only(['search', 'policytype', 'status', 'datefrom', 'dateto', 'agentcode']);
        $query        = policy::query();

        if (in_array($user->role, ['agent', 'subagent'])) {
            $query->where('agent_id', $user->id);
        }

        // Free-text search across policy no., insured name, and vehicle reg no.
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('policyno', 'like', "%{$term}%")
                  ->orWhere('insured_name', 'like', "%{$term}%")
                  ->orWhereIn('id', function ($sub) use ($term) {
                      $sub->select('policyid')
                          ->from('policyrisks')
                          ->where('regno', 'like', "%{$term}%");
                  });
            });
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

        $policies = $query->orderBy('updated_at', 'desc')
            ->with(['risk', 'agentUser'])
            ->paginate(50)
            ->withQueryString();
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
            $agent      = agentsdetailsModel::where('uid', $policy->agent_id)->first();
            $creditType = $this->creditType($policy->producttype);
            $creditLog  = '';

            // Refund credit only if the policy was NOT paid by card
            if ($policy->getsuccesspayments()->count() == 0) {
                if ($agent && $agent->issubagent == false) {
                    // Regular agent — refund typed counter and sync total
                    if ($creditType === 'private') {
                        $agent->private_used = max(0, $agent->private_used - 1);
                    } else {
                        $agent->commercial_used = max(0, $agent->commercial_used - 1);
                    }
                    $agent->syncTotals();
                    $agent->save();
                    $creditLog = " | {$creditType} credit refunded. noused now: {$agent->noused}";
                } elseif ($agent) {
                    // Subagent — check if parent uses pool mode
                    $parentAgent = $agent->parentAgentDetails();
                    if ($parentAgent && $parentAgent->pool_enabled) {
                        if ($creditType === 'private') {
                            $parentAgent->pool_private_used = max(0, $parentAgent->pool_private_used - 1);
                            $parentAgent->save();
                            if ($agent->pool_cap_private > 0) {
                                $agent->pool_cap_used_private = max(0, $agent->pool_cap_used_private - 1);
                                $agent->save();
                            }
                        } else {
                            $parentAgent->pool_commercial_used = max(0, $parentAgent->pool_commercial_used - 1);
                            $parentAgent->save();
                            if ($agent->pool_cap_commercial > 0) {
                                $agent->pool_cap_used_commercial = max(0, $agent->pool_cap_used_commercial - 1);
                                $agent->save();
                            }
                        }
                        $creditLog = " | Pool {$creditType} refund";
                    } else {
                        // Individual allocation mode — refund typed counter and sync total
                        if ($creditType === 'private') {
                            $agent->subcreditused_private = max(0, $agent->subcreditused_private - 1);
                        } else {
                            $agent->subcreditused_commercial = max(0, $agent->subcreditused_commercial - 1);
                        }
                        $agent->syncSubcreditTotals();
                        $agent->save();
                        $creditLog = " | {$creditType} subcredit refunded. total used: {$agent->subcreditused}";
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

    /** Abort 403 unless the signed-in user may perform $ability on $policy (see PolicyPolicy). */
    private function authorizePolicy(string $ability, policy $policy): void
    {
        abort_unless(Gate::allows($ability, $policy), 403);
    }
}
