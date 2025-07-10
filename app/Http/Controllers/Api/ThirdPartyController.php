<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\vehicleMake;
use App\Models\vehicleModel;
use App\Models\policy;
use App\Models\policyrisk;
use App\Models\states;
use App\Models\vehiclecolor;
use App\Models\agentsdetailsModel;
use App\Jobs\PostNIIPDataSlow; // Import the job class


class ThirdPartyController extends Controller
{
    //
    public function newpolicy(Request $request)
    {
        //TO DO:Validate the request data
        $rules = [
    'fname' => 'required|string',
    'lname' => 'required|string',
    'email' => 'required|email',
    'gender' => 'required|in:Male,Female,Other',
    'dob' => 'required|date_format:Y-m-d',
    'phone' => 'required|string',
    'state' => 'required|integer|exists:states,stateid',
    'lga' => 'required|integer|exists:lgas,lgaid',
    'address' => 'required|string',
    'vehicleuse' => 'required|string|in: privatemotor,commercialmotor,motorcycle',
    'vehiclemakeid' => 'required|integer|exists:vehicle_makes,niipvmid',
    'vehiclemodelid' => 'required|integer|exists:vehicle_models,vmodelid',
    'vehiclecolor' => 'required|integer|exists:vehiclecolors,colorid',
    'regno' => 'required|string',
    'engineno' => 'required|string',
    'chassisno' => ['required', 'regex:/^[^IO]*$/'],
    'yearofmake' => 'required|digits:4',
];

$messages = [
    'chassisno.regex' => 'The chassis number must not contain the letters "I" or "O".',
];

$validator = Validator::make($request->all(), $rules, $messages);

if ($validator->fails()) {
    return response()->json([
        'status' => false,
        'message' => 'Validation failed. Please correct the highlighted fields.',
        'errors' => $validator->errors(),
    ], 422);
};
 

        //First create new user account if phonenumber is unique 

         $insured=User::where('telno',$request->phone)->first();
         $fullname= $request->fname. "  ".$request->lname;

            if (empty($insured)) {

                $genpassword='Password';
                
                # create new insured user profile
                $insured= new User();
                $insured->firstname=$request->fname;
                $insured->lastname=$request->lname;
                $insured->name=$fullname;
                $insured->email=$request->email;
                $insured->gender=$request->gender;
                $insured->dob=$request->dob;
                $insured->telno=$request->phone;
                $insured->state=$request->state;
                $insured->address=$request->address;
                $insured->stateid=$request->state;
                $insured->lgaid=$request->lga;
                $insured->password=Hash::make($genpassword);


                $insured->save();


            } else {
                # Map policy to existing user...
                
            }
        //Second Get NIIP Use Code
         switch ($request->vehicleuse) {
            case ('privatemotor'):
                # began the purchase of a private motor policy
                $producttype='Private Motor Third Party';
                $contribution=15000;
                $usekey='private';
                 $insurancetype='Private';
                $vehicleuse="car";
                $niipusecode= 3; // Private Motor
                break;

            case ('commercialmotor'):
                # began the purchase of a Commercial  motor policy
                $producttype='Commercial Motor Third Party';
                $contribution=20000;
                $usekey='commercial';
                $insurancetype='Commercial';
                $vehicleuse="car";
                $niipusecode= 8; // Commercial Motor
                break;
            case ('motorcycle'):
                # began the purchase of a Motorcycle policy
                $producttype='Motorcycle Third Party';
                $contribution=5000;
                $usekey='commercial';
                 $insurancetype='Commercial';
                $vehicleuse="motorcycle";
                $niipusecode= 4;  // Motorcycle
                break;
            default:
                # To Do  create a default 
                return back()->with('Error', 'Product not Configured imported successfully.');
                break;
        }    
             
        // Third Create Motor Policy 
            $start_date=date_create();
            $end_date=date_add(date_create(),date_interval_create_from_date_string("1 year"));

            #GET Vehicle Make and Model To BE USED with NIIP integration
            $vmake=vehicleMake::where('niipvmid',$request->vehiclemakeid )->first();
            $vmodel=vehicleModel::where('vmodelid',$request->vehiclemodelid)->first();

            #Get name of Agent using this channel
            $user=Auth::user();

            $policy= new policy();

            $policy->firstname=$request->fname;
            $policy->lastname=$request->lname;
            $policy->telno=$request->phone;
            $policy->email=$request->email;
            $policy->insured_id=$insured->id;
            $policy->producttype=$producttype;
            $policy->insured_name=$fullname;
            $policy->agent_id=$user->id;
            $policy->status='draft';
            $policy->start_date= date_format($start_date,'Y/m/d'); 
            $policy->end_date= date_format($end_date,'Y/m/d');
            $policy->create_uid=$user->id;
            $policy->update_uid=$user->id;
            $policy->usekey=$usekey;
            ##TO DO Create Method to Calc Agents Commission and Contribution
            $policy->contribution=$contribution;
            $policy->commission=0;
            $policy->insurancetype=$insurancetype;
            $policy->vehicleuse=$vehicleuse;
            $policy->stateid=$request->state;
            $policy->lgaid=$request->lga;
            $policy->niipvehicleuse=$niipusecode;
            
             $policy->save();

                         #Create New Policy Risk Object
            $policyrisk=new policyrisk();
            #TO DO product ID
            $policyrisk->product_id=1;
            $policyrisk->regno=$request->regno;
            $policyrisk->policyid=$policy->id;
            $policyrisk->engineno=$request->engineno;
            $policyrisk->chassisno=$request->chassisno;
            $policyrisk->vehiclemake=$vmake->vmake;
            $policyrisk->vehiclemodel=$vmodel->vmodelname;
            $policyrisk->yearofmake=$request->yearofmake;
            $policyrisk->vechiclecolorid=$request->vehiclecolor;
            $policyrisk->vehiclecolor=vehiclecolor::where('colorid',$request->vehiclecolor)->first()->color;

            if ($policy->producttype=='Private Motor Third Party') {
                # code...
                $policy->vehicleuse='car';
                    $policy->insurancetype='Private';
                    $policyrisk->contribution=15000;
            }
            else if ($policy->producttype=='Commercial Motor Third Party') {
                # code...
                $policy->vehicleuse='car';
                $policy->insurancetype='Commercial';
                $policyrisk->contribution=20000;
            } elseif ($policy->producttype=='Motorcycle Third Party') {
                # code...
                $policy->vehicleuse='motorcycle';
                $policy->insurancetype='Motorcycle';
                $policyrisk->contribution=5000;
            }

            $policyrisk->save();
                            #Validation of mandatory Field with default values
                $gsm=$insured->telno;
                if (empty($gsm)) {
                    # change Gsm to company number
                    $gsm='+234 806 565 7291';
                }
                #Use the ELite API and push data


                $policydata=[
                        "fullName"=>$policy->insured_name,
                        "ContactAddress" => $insured->address,  
                        "mobileNumber"=> $gsm,
                        "Email"=> $insured->email,
                        "engineNumber"=> $policyrisk->engineno,
                        "chassisNumber"=> $policyrisk->chassisno,
                        "vehicleColor"=> $policyrisk->vehiclecolor,
                         "yearOfMake"=> strval($policyrisk->yearofmake),
                        "vehicleMake"=> $policyrisk->vehiclemake,
                        "registrationNumber"=> $policyrisk->regno,
                        "vehicleType"=> $policy->vehicleuse,
                        "engineCapacity"=> "1.6L",
                        "vehicleModel"=> $policyrisk->vehiclemodel,
                        "useOFVehicle"=>'n/a',
                        "insuranceType"=>$policy->usekey
                ];

                $policydatajSon=json_encode($policydata);
              
                #To Get individual AUTH TOKEN
                #1.  Check if the Agent has an Access Token From Elite
                #2.  IF no AUTH TOKEN USE default users TOKEN
                $agent=agentsdetailsModel::where('uid', $user->id)->first();
                $token=$agent->auth_token;
               
                if (empty($token)) {
                    $accesstoken=config('variables.API_ELITE_TOKEN');
                } else {
                   $accesstoken=$token;
                }
                

                $response = Http::withHeader('Auth-Token',$accesstoken)->withBody($policydatajSon)
                ->post(config('variables.API_ELITE_URL'));
                           #handle response from elite check status for success/fail
          $policy->elite_msg=$response->body();
          

            // Decode JSON string into an associative array
            $data = json_decode($response->body(), true);

            if ($data['data']['status'] == 'success') {
                # code...

                $policy->elite_msg=$data['data']['status'] .$data['data']['message'];
                $policy->policyno=$data['data']['policy_number'];
                $policy->status='approved';
                $policy->save();

                          #TO DO Upload policy to NIIP

                #Prepare Third Party Motor Policy API Data for NIIP

                $niipdata=
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
   

                $policy->elite_msg=$response->body();
                $policy->elite_msg=$data['data']['status'] .$data['data']['message'] ;
                $policy->policyno='';
                $policy->status='failed';
                $policy->save();
                
                $errors=$policy->elite_msg;
                $id=$policy->id;
            }

        //TODO: Success Response
        return response()->json([
            'status' => 'success',
            'message' => 'Policy created successfully',
            'data' => [
                'insured_id' => $insured->id,
                'insured_name' => $insured->name,
                'policy_number' => $policy->policyno,
                'certificate_url'=>'http://elitepolicy.salamtakafulinsurance.com/api/v1/policy/view-certificate?policy_no='.$policy->policyno
            ]
        ], 201);



    }

}