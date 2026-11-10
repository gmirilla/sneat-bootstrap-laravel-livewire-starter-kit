<?php

namespace App\Http\Controllers;

use App\Models\policy;
use App\Models\paystacktransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaystacktransactionController extends Controller
{
    /**
     * Create a new Paystack transaction. Called internally by PolicyController with server-built
     * data; the amount is always taken from the policy, never from the request.
     */
    public function create_paystack_transaction(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|max:255',
            'amount' => 'required|numeric',
            'policy_id' => 'required|integer|exists:policies,id',
        ]);

        $validatedData['amount'] = policy::findOrFail($validatedData['policy_id'])->contribution;
        $validatedData['ref_id'] = 'paystack_' . Str::uuid()->toString();

        $transaction = paystacktransaction::create($validatedData);

        // Init Transaction with paystack and get access code for transaction
        try {
            $response = Http::withToken(config('variables.PAYSTACK_SECRET_KEY'))
                ->asForm()
                ->timeout(30)
                ->post('https://api.paystack.co/transaction/initialize', [
                    'email'  => $transaction->email,
                    'amount' => (int) round($transaction->amount * 100),
                ]);
            $paystackresponse = $response->object();
        } catch (\Exception $e) {
            Log::error('Paystack initialize failed', ['policy_id' => $transaction->policy_id, 'error' => $e->getMessage()]);
            $paystackresponse = null;
        }

        if (($paystackresponse->status ?? false) === true && !empty($paystackresponse->data->access_code)) {
            $transaction->access_code = $paystackresponse->data->access_code;
            $transaction->reference_code = $paystackresponse->data->reference;
            $transaction->save();
            return response($transaction->access_code, 201);
        }

        $transaction->access_code = "N/A";
        $transaction->reference_code = "N/A";
        $transaction->save();
        return response("Error initializing transaction", 500);
    }

    /**
     * Ask Paystack for the status of a reference. Always returns a JSON body shaped like
     * Paystack's ({status, data:{status, amount, currency, ...}}); on any failure `data` is absent.
     */
    public function verify_payment(string $reference_code)
    {
        $transaction = paystacktransaction::where('reference_code', $reference_code)->first();

        if (!$transaction) {
            return response()->json(['status' => false, 'message' => 'Unknown transaction reference'], 404);
        }

        try {
            $response = Http::withToken(config('variables.PAYSTACK_SECRET_KEY'))
                ->timeout(30)
                ->get('https://api.paystack.co/transaction/verify/' . rawurlencode($transaction->reference_code));

            return response($response->body(), 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Paystack verify failed', ['reference' => $reference_code, 'error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Could not reach Paystack'], 502);
        }
    }
}
