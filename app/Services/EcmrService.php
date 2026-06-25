<?php

namespace App\Services;

use App\Models\ecmr;
use Illuminate\Support\Facades\Log;

class EcmrService
{
    public function __construct(private ProxyClient $proxy) {}

    /**
     * Run an ECMR lookup for the given registration number and save the result.
     *
     * Returns true on a successful API lookup, false on any failure.
     * A failure record is always saved so the outcome is visible in the policy list.
     */
    public function check(string $regno, ?int $policyId, ?int $cuid = null): bool
    {
        if (!$this->proxy->isConfigured()) {
            Log::error('EcmrService: proxy not configured', ['regno' => $regno]);
            $this->saveRecord($regno, $policyId, $cuid, 'check_failed', 'N/A', null, 'ECMR proxy not configured (PROXY_SECRET / PROXY_URL missing).');
            return false;
        }

        $proxyUrl = $this->proxy->getBaseUrl();

        try {
            $loginBody = $this->proxy->call('POST', $proxyUrl . '/api/ecmr/login');

            if ($loginBody === null) {
                $this->saveRecord($regno, $policyId, $cuid, 'check_failed', 'N/A', null, 'ECMR login request failed: no response from proxy.');
                return false;
            }

            $loginData = json_decode($loginBody);

            if (!isset($loginData->statusCode) || $loginData->statusCode != 0) {
                $msg = $loginData->message ?? 'Unknown login error';
                $this->saveRecord($regno, $policyId, $cuid, 'check_failed', 'N/A', null, 'ECMR login failed: ' . $msg);
                return false;
            }

            $lookupUrl  = $proxyUrl . '/api/ecmr/lookup?token=' . urlencode($loginData->data->token) . '&regno=' . urlencode($regno);
            $lookupBody = $this->proxy->call('GET', $lookupUrl);

            if ($lookupBody === null) {
                $this->saveRecord($regno, $policyId, $cuid, 'check_failed', 'N/A', null, 'ECMR lookup request failed: no response from proxy.');
                return false;
            }

            $result = json_decode($lookupBody);

            $this->saveRecord(
                $regno,
                $policyId,
                $cuid,
                $result->data->cmr_status ?? 'Unknown',
                $result->data->cmr_number ?? 'N/A',
                $lookupBody,
                $result->message ?? ''
            );

            return true;

        } catch (\Exception $e) {
            Log::error('EcmrService: unexpected error', ['regno' => $regno, 'error' => $e->getMessage()]);
            $this->saveRecord($regno, $policyId, $cuid, 'check_failed', 'N/A', null, 'ECMR check error: ' . $e->getMessage());
            return false;
        }
    }

    private function saveRecord(string $regno, ?int $policyId, ?int $cuid, string $status, string $cmrNumber, ?string $response, string $message): void
    {
        $record                = new ecmr();
        $record->licence_plate = $regno;
        $record->policy_id     = $policyId;
        $record->cuid          = $cuid;
        $record->status        = $status;
        $record->cmr_number    = $cmrNumber;
        $record->response      = $response;
        $record->message       = $message;
        $record->save();
    }
}
