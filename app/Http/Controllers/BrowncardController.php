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

        // NIIP doesn't cover every vehicle type — fall back to NIID (a separate national
        // registry) when NIIP has no record at all for this plate.
        $live = $this->tryBothFormats(fn (string $r) => $this->fetchFromNiip($r), $value, $normalized)
            ?? $this->tryBothFormats(fn (string $r) => $this->fetchFromNiid($r), $value, $normalized);

        if ($record) {
            // A local certificate exists, but if a live registry explicitly confirms this
            // policy is no longer active, don't hand out a certificate for an expired/cancelled policy.
            if ($live && !$live['active']) {
                return null;
            }

            return $this->buildResult(
                source: 'local',
                policyNumber: $record->policynumber,
                regNo: $record->regno,
                brownCardNumber: $record->browncardnumber,
                policyHolder: $live['policyHolder'] ?? null,
                vehicleMake: $live['vehicleMake'] ?? null,
                vehicleModel: $live['vehicleModel'] ?? null,
                issueDate: $live['issueDate'] ?? null,
                expiryDate: $live['expiryDate'] ?? null,
                verifiedLive: (bool) ($live['active'] ?? false),
            );
        }

        // No local record — rely entirely on a live registry response.
        if ($live && $live['active'] && !empty($live['brownCardNumber'])) {
            return $this->buildResult(
                source: $live['source'],
                policyNumber: $live['policyNumber'],
                regNo: $live['regNo'] ?: $value,
                brownCardNumber: $live['brownCardNumber'],
                policyHolder: $live['policyHolder'] ?? null,
                vehicleMake: $live['vehicleMake'] ?? null,
                vehicleModel: $live['vehicleModel'] ?? null,
                issueDate: $live['issueDate'] ?? null,
                expiryDate: $live['expiryDate'] ?? null,
                verifiedLive: true,
            );
        }

        return null;
    }

    /**
     * Try a live-registry fetch with the raw input first, then the normalized form if
     * they differ — the policy may have been submitted with the same messy formatting
     * the agent typed.
     */
    private function tryBothFormats(callable $fetcher, string $raw, string $normalized): ?array
    {
        return $fetcher($raw) ?? ($raw !== $normalized ? $fetcher($normalized) : null);
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

    /**
     * Query NIIP's live "active policy" endpoint. Returns null when NIIP has no
     * record at all for this plate (statusCode 02) or is unreachable — either way
     * the caller should fall back to NIID rather than treating it as confirmed-inactive.
     */
    private function fetchFromNiip(string $regNo): ?array
    {
        $configuredUrl = config('variables.NIIP_URL');
        $apiKey        = config('variables.NIIP_API_KEY');

        if (empty($configuredUrl) || empty($apiKey)) {
            return null;
        }

        // NIIP_URL is configured as the full "generate policy" endpoint (e.g.
        // https://niip.ng/api/getPolicyByInsuranceCompany), not a bare base URL —
        // derive scheme+host so this hits the correct NIIP host regardless of that path.
        $parsed = parse_url($configuredUrl);
        if (empty($parsed['scheme']) || empty($parsed['host'])) {
            return null;
        }
        $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

        try {
            $response = Http::timeout(15)->get($baseUrl . '/api/getActiveInsurancePolicy', [
                'apiKey' => $apiKey,
                'regNo'  => $regNo,
            ]);

            // NIIP encodes success/failure in the body's isSuccess field, not purely via
            // HTTP status — error responses (e.g. "Registration Number is invalid") come
            // back as HTTP 400 with a valid JSON body, so parse regardless of status code.
            $body = $response->json();
        } catch (\Exception $e) {
            Log::error('BrowncardController: NIIP live lookup failed', ['regNo' => $regNo, 'error' => $e->getMessage()]);
            return null;
        }

        if (!is_array($body) || ($body['statusCode'] ?? null) === '02') {
            return null;
        }

        $data = $body['data'] ?? [];

        return [
            'source'          => 'niip',
            'active'          => (bool) ($body['isSuccess'] ?? false),
            'policyNumber'    => $data['policyNumber'] ?? null,
            'regNo'           => $data['registrationNumber'] ?? null,
            'brownCardNumber' => $data['brownCardPolicyNumber'] ?? null,
            'policyHolder'    => $data['policyHolder'] ?? null,
            'vehicleMake'     => $data['vehicleMake'] ?? null,
            'vehicleModel'    => $data['vehicleModel'] ?? null,
            'issueDate'       => $data['issueDate'] ?? null,
            'expiryDate'      => $data['expiryDate'] ?? null,
        ];
    }

    /**
     * Query NIID (a separate national registry from NIIP) — covers vehicle types/
     * policies that don't show up in NIIP. Returns null when NIID has no record at
     * all for this plate.
     */
    private function fetchFromNiid(string $regNo): ?array
    {
        $baseUrl  = config('variables.NIID_URL');
        $username = config('variables.NIID_USERNAME');
        $password = config('variables.NIID_PASSWORD');

        if (empty($baseUrl) || empty($username) || empty($password)) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get(rtrim($baseUrl, '/') . '/verify', [
                'sv' => $regNo,
                'st' => 'RegistrationNumber',
                'un' => $username,
                'pw' => $password,
            ]);

            $data = $response->json();
        } catch (\Exception $e) {
            Log::error('BrowncardController: NIID live lookup failed', ['regNo' => $regNo, 'error' => $e->getMessage()]);
            return null;
        }

        if (!is_array($data)) {
            return null;
        }

        $status = $data['LicenseStatus'] ?? null;

        if (in_array($status, ['NOT FOUND', 'INVALID STRING VALUE', null], true)) {
            return null;
        }

        return [
            'source'          => 'niid',
            'active'          => $status === 'OK' && !empty($data['ECOWASBrownCardCertNo']),
            'policyNumber'    => $data['PolicyNumber'] ?? null,
            'regNo'           => $data['NewRegistrationNumber'] ?: ($data['RegistrationNumber'] ?? null),
            'brownCardNumber' => $data['ECOWASBrownCardCertNo'] ?? null,
            'policyHolder'    => $data['InsuredName'] ?? null,
            'vehicleMake'     => $data['VehicleMake'] ?? null,
            'vehicleModel'    => $data['VehicleModel'] ?? null,
            'issueDate'       => $data['IssueDate'] ?? null,
            'expiryDate'      => $data['ExpiryDate'] ?? null,
        ];
    }

    private function normalizeRegNo(string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }
}
