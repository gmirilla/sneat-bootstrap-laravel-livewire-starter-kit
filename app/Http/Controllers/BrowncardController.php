<?php

namespace App\Http\Controllers;

use App\Models\browncard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class BrowncardController extends Controller
{
    public function showLookup()
    {
        return view('browncard.lookup');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'search_type'  => 'required|in:policy_no,reg_no',
            'search_value' => 'required|string|max:50',
        ]);

        $value = trim($request->search_value);

        $result = $request->search_type === 'policy_no'
            ? $this->lookupByPolicyNumber($value)
            : $this->lookupByRegNo($value);

        if (!$result) {
            return back()->withInput()->with('lookup_error', 'No brown card certificate was found for the details provided. Please double-check and try again.');
        }

        return view('browncard.lookup', ['result' => $result]);
    }

    public function download(Request $request)
    {
        abort_unless($request->hasValidSignature(), 403);

        $refId = $request->query('ref');
        abort_unless($refId, 404);

        try {
            $response = Http::timeout(30)->get('https://api.browncardnigeria.org/api/Policies/GetPdf', [
                'refId' => $refId,
            ]);
        } catch (\Exception $e) {
            Log::error('BrowncardController: certificate download failed', ['ref' => $refId, 'error' => $e->getMessage()]);
            abort(502, 'Unable to retrieve the certificate right now. Please try again shortly.');
        }

        if (!$response->successful()) {
            abort(502, 'Unable to retrieve the certificate right now. Please try again shortly.');
        }

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="brown-card-' . preg_replace('/[^A-Za-z0-9]/', '', $refId) . '.pdf"',
        ]);
    }

    private function lookupByPolicyNumber(string $value): ?array
    {
        $record = browncard::where('policynumber', strtoupper($value))
            ->whereNotNull('browncardnumber')
            ->where('elitesuccess', true)
            ->first();

        if (!$record) {
            return null;
        }

        return $this->buildResult(
            source: 'local',
            policyNumber: $record->policynumber,
            regNo: $record->regno,
            brownCardNumber: $record->browncardnumber,
        );
    }

    private function lookupByRegNo(string $value): ?array
    {
        $normalized = $this->normalizeRegNo($value);

        $record = browncard::whereRaw(
                "UPPER(REPLACE(REPLACE(REPLACE(REPLACE(regno, ' ', ''), '-', ''), '.', ''), '/', '')) = ?",
                [$normalized]
            )
            ->whereNotNull('browncardnumber')
            ->where('elitesuccess', true)
            ->first();

        // Try NIIP's live endpoint with the raw input first, then the normalized form —
        // the policy may have been submitted to NIIP with the same messy formatting the agent typed.
        $live = $this->fetchLiveByRegNo($value)
            ?? ($value !== $normalized ? $this->fetchLiveByRegNo($normalized) : null);

        if ($record) {
            // A local certificate exists, but if we could reach NIIP and it says the policy
            // is no longer valid, don't hand out a certificate for an expired/cancelled policy.
            if ($live && !($live['isSuccess'] ?? false)) {
                return null;
            }

            $data = $live['data'] ?? [];

            return $this->buildResult(
                source: 'local',
                policyNumber: $record->policynumber,
                regNo: $record->regno,
                brownCardNumber: $record->browncardnumber,
                policyHolder: $data['policyHolder'] ?? null,
                vehicleMake: $data['vehicleMake'] ?? null,
                vehicleModel: $data['vehicleModel'] ?? null,
                issueDate: $data['issueDate'] ?? null,
                expiryDate: $data['expiryDate'] ?? null,
                verifiedLive: (bool) $live,
            );
        }

        // No local record — rely entirely on the live NIIP response.
        if ($live && ($live['isSuccess'] ?? false)) {
            $data            = $live['data'] ?? [];
            $brownCardNumber = $data['brownCardPolicyNumber'] ?? null;

            if (!$brownCardNumber) {
                return null;
            }

            return $this->buildResult(
                source: 'niip',
                policyNumber: $data['policyNumber'] ?? null,
                regNo: $data['registrationNumber'] ?? $value,
                brownCardNumber: $brownCardNumber,
                policyHolder: $data['policyHolder'] ?? null,
                vehicleMake: $data['vehicleMake'] ?? null,
                vehicleModel: $data['vehicleModel'] ?? null,
                issueDate: $data['issueDate'] ?? null,
                expiryDate: $data['expiryDate'] ?? null,
                verifiedLive: true,
            );
        }

        return null;
    }

    private function buildResult(
        string $source,
        ?string $policyNumber,
        ?string $regNo,
        string $brownCardNumber,
        ?string $policyHolder = null,
        ?string $vehicleMake = null,
        ?string $vehicleModel = null,
        ?string $issueDate = null,
        ?string $expiryDate = null,
        bool $verifiedLive = false,
    ): array {
        return [
            'source'          => $source,
            'policyNumber'    => $policyNumber,
            'regNo'           => $regNo,
            'brownCardNumber' => $brownCardNumber,
            'policyHolder'    => $policyHolder,
            'vehicleMake'     => $vehicleMake,
            'vehicleModel'    => $vehicleModel,
            'issueDate'       => $issueDate,
            'expiryDate'      => $expiryDate,
            'verifiedLive'    => $verifiedLive,
            'downloadUrl'     => URL::temporarySignedRoute(
                'browncard.download',
                now()->addMinutes(5),
                ['ref' => $brownCardNumber]
            ),
        ];
    }

    private function fetchLiveByRegNo(string $regNo): ?array
    {
        $baseUrl = config('variables.NIIP_URL');
        $apiKey  = config('variables.NIIP_API_KEY');

        if (empty($baseUrl) || empty($apiKey)) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get(rtrim($baseUrl, '/') . '/api/getActiveInsurancePolicy', [
                'apiKey' => $apiKey,
                'regNo'  => $regNo,
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('BrowncardController: NIIP live lookup failed', ['regNo' => $regNo, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function normalizeRegNo(string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }
}
