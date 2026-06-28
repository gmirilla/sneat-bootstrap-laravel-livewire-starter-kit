<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProxyClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class EliteBrokerController extends Controller
{
    public function __construct(private readonly ProxyClient $proxy) {}

    public function index()
    {
        $brokers = [];
        $error   = null;

        if (!$this->proxy->isConfigured()) {
            $error = 'Elite proxy is not configured (PROXY_URL / PROXY_SECRET missing from .env).';
        } else {
            $raw = $this->proxy->call('GET', $this->proxy->getBaseUrl() . '/api/elite/brokers');

            if ($raw === null) {
                $error = 'No response from the Elite proxy. Check PROXY_URL and server availability.';
            } else {
                $json = json_decode($raw, true);

                if (($json['status'] ?? '') === 'success') {
                    $brokers = $json['data'] ?? [];
                } else {
                    $error = $json['message'] ?? 'Unexpected response from Elite proxy.';
                    Log::error('EliteBrokerController@index: proxy error', ['body' => $raw]);
                }
            }
        }

        // Keyed by broker_id so the view can check O(1) per row
        $brokerAccounts = User::whereNotNull('broker_id')
            ->where('role', 'broker')
            ->get(['broker_id', 'email', 'account_status'])
            ->keyBy('broker_id');

        return view('elite.brokers', compact('brokers', 'error', 'brokerAccounts'));
    }

    public function policies(Request $request)
    {
        $request->validate(['broker_id' => 'required|integer|min:1']);

        if (!$this->proxy->isConfigured()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Elite proxy is not configured.',
                'data'    => [],
            ], 502);
        }

        $url = $this->proxy->getBaseUrl()
             . '/api/elite/broker/policies?broker_id='
             . $request->integer('broker_id');

        $raw = $this->proxy->call('GET', $url);

        if ($raw === null) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No response from the Elite proxy.',
                'data'    => [],
            ], 502);
        }

        $json = json_decode($raw, true);

        if ($json === null) {
            Log::error('EliteBrokerController@policies: invalid JSON', ['url' => $url, 'raw' => $raw]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid response from Elite proxy.',
                'data'    => [],
            ], 502);
        }

        return response()->json($json);
    }

    public function createBrokerAccount(Request $request)
    {
        $request->validate([
            'broker_id'  => 'required|integer|unique:users,broker_id',
            'name'       => 'required|string|max:150',
            'email'      => 'required|email|max:150|unique:users,email',
            'phone'      => 'nullable|string|max:30',
            'send_reset' => 'nullable|boolean',
        ]);

        $nameParts = explode(' ', trim($request->name), 2);

        $user = User::create([
            'name'           => $request->name,
            'firstname'      => $nameParts[0],
            'lastname'       => $nameParts[1] ?? '',
            'email'          => $request->email,
            'telno'          => $request->phone ?? '',
            'password'       => Hash::make(Str::random(24)),
            'role'           => 'broker',
            'broker_id'      => $request->integer('broker_id'),
            'account_status' => 'active',
        ]);

        if ($request->boolean('send_reset')) {
            Password::broker()->sendResetLink(['email' => $user->email]);
        }

        return redirect()->route('elite.brokers')
            ->with('success', "Account created for {$user->name} (Broker #{$request->broker_id})."
                . ($request->boolean('send_reset') ? ' Password setup email sent.' : ''));
    }
}
