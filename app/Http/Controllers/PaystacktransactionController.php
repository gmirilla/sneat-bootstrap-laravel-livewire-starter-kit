<?php

namespace App\Http\Controllers;

use App\Models\paystacktransaction;
use Illuminate\Http\Request;

class PaystacktransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Create a new Paystack transaction.
     */
    public function create_paystack_transaction(Request $request)
    {


            $validatedData = $request->validate([
            'email' => 'required|email|max:255',
            'amount' => 'required|numeric',
            'policy_id' => 'required|integer|exists:policies,id',
        ]);
        //create unique ref_id
        $validatedData['ref_id'] = uniqid('paystack_');

        $transaction = paystacktransaction::create($validatedData);

        // Init Transaction with paystack and get access code for transaction
        $url = "https://api.paystack.co/transaction/initialize";
        $fields = [
            'email' => $transaction->email,
            'amount' => $transaction->amount*100
        ];

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
            "Cache-Control: no-cache",
        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);


        //To do : process result and store transaction access code for uselater

         //test to convert result to jsonbjec
        $paystackresponse=json_decode($result);
        
    if ($paystackresponse->status == true) {
        $transaction->access_code=$paystackresponse->data->access_code;
        $transaction->reference_code=$paystackresponse->data->reference;
        $transaction->save();
        return response($transaction->access_code,201);
    }
    else{
                $transaction->access_code="N/A";
        $transaction->reference_code="N/A";
        $transaction->save();
        return response("Error initializing transaction",500);
        
    }


    }
      /**
     * Verify & Update the payment status for resource.
     */
    public function verify_payment(string $reference_code)
    {
        $transaction=paystacktransaction::where('reference_code', $reference_code)->first();
          $curl = curl_init();
          $url="https://api.paystack.co/transaction/verify/".$transaction->reference_code;
  
  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
      "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
      "Cache-Control: no-cache",
    ),
  ));
  
  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);
  
  if ($err) {
    $result=$err;
  } else {
    $result= $response;
  }
  return response($result);
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
    public function show(paystacktransaction $paystacktransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(paystacktransaction $paystacktransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, paystacktransaction $paystacktransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(paystacktransaction $paystacktransaction)
    {
        //
    }
}
