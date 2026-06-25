<?php

namespace App\Services;

use App\Models\ecmr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcmrService
{
    /**
     * Run an ECMR lookup for the given registration number and save the result.
     *
     * Returns true on a successful API lookup, false on any failure.
     * A failure record is always saved so the outcome is visible in the policy list.
     */
    public function check(string $regno, ?int $policyId, ?int $cuid = null): bool
    {
        $secret   = config('variables.PROXY_SECRET') ?: env('PROXY_SECRET');
        $proxyUrl = rtrim(config('variables.PROXY_URL') ?: env('PROXY_URL'), '/');

        if (empty($secret) || empty($proxyUrl)) {
            Log::error('EcmrService: proxy not configured', ['regno' => $regno]);
            $this->saveRecord($regno, $policyId, $cuid, 'check_failed', 'N/A', null, 'ECMR proxy not configured (PROXY_SECRET / PROXY_URL missing).');
            return false;
        }

        try {
            $loginBody = $this->curlExec('POST', $proxyUrl . '/api/ecmr/login', $secret);

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
            $lookupBody = $this->curlExec('GET', $lookupUrl, $secret);

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
        $record               = new ecmr();
        $record->licence_plate = $regno;
        $record->policy_id    = $policyId;
        $record->cuid         = $cuid;
        $record->status       = $status;
        $record->cmr_number   = $cmrNumber;
        $record->response     = $response;
        $record->message      = $message;
        $record->save();
    }

    /**
     * Execute an HTTP call via the system curl binary on Linux (bypasses PHP's stale CA bundle)
     * and via Laravel's Http client on Windows (local dev).
     */
    private function curlExec(string $method, string $url, string $secret): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                $response = Http::withHeaders([
                    'X-Proxy-Secret' => $secret,
                    'Accept'         => 'application/json',
                ])->timeout(30)->{strtolower($method)}($url);

                $body = $response->body();
                return ($body !== '') ? $body : null;
            } catch (\Exception $e) {
                Log::error('EcmrService Http error: ' . $e->getMessage());
                return null;
            }
        }

        $escapedUrl    = escapeshellarg($url);
        $escapedSecret = escapeshellarg('X-Proxy-Secret: ' . $secret);
        $methodFlag    = strtoupper($method) === 'POST' ? '-X POST' : '-X GET';
        $cmd           = "curl -s --max-time 30 {$methodFlag} {$escapedUrl} -H {$escapedSecret} -H " . escapeshellarg('Accept: application/json');
        $output        = shell_exec($cmd);

        return ($output !== null && $output !== '') ? $output : null;
    }
}
