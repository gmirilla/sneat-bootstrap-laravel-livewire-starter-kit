<?php

namespace App\Jobs;

use App\Models\browncard;
use App\Models\policy;
use App\Services\ProxyClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostNIIPDataSlow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function handle(): void
    {
        $policy = policy::where('policyno', $this->data['PolicyNumber'])->first();

        if (!$policy) {
            Log::error('PostNIIPDataSlow: policy not found', ['policyno' => $this->data['PolicyNumber']]);
            return;
        }

        try {
            $niipResponse     = Http::withBody(json_encode($this->data))->timeout(180)->post(config('variables.NIIP_URL'));
            $niipResponseBody = $niipResponse->body();
            $niipResponseData = json_decode($niipResponseBody, true);

            $policy->niip_status = $niipResponseBody;
            $policy->save();

            Log::info('PostNIIPDataSlow: NIIP response received', ['policyno' => $policy->policyno]);

            if ($niipResponseData['isSuccess'] ?? false) {
                $brownCardNo = $niipResponseData['brownCardPolicyNumber'] ?? null;

                if ($brownCardNo) {
                    $this->syncBrowncard($policy, $brownCardNo);
                }

                CheckEcmrJob::dispatch($this->data['RegNo'], $policy->id);
            }

        } catch (\Exception $e) {
            $policy->niip_status = 'Error: ' . $e->getMessage();
            $policy->save();
            Log::error('PostNIIPDataSlow: NIIP request failed', [
                'policyno' => $policy->policyno,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    private function syncBrowncard(policy $policy, string $brownCardNo): void
    {
        $record = browncard::create([
            'policyid'        => $policy->id,
            'policynumber'    => $policy->policyno,
            'regno'           => $this->data['RegNo'] ?? null,
            'browncardnumber' => $brownCardNo,
            'elitesuccess'    => false,
            'elitemsg'        => null,
        ]);

        $proxy = app(ProxyClient::class);

        if (!$proxy->isConfigured()) {
            Log::warning('PostNIIPDataSlow: proxy not configured, skipping Elite browncard update', [
                'policyno' => $policy->policyno,
            ]);
            return;
        }

        $url  = rtrim($proxy->getBaseUrl(), '/') . '/api/elite/policy/risk/cert-airworthiness';
        $raw  = $proxy->postJson($url, [
            'policy_no'             => $policy->policyno,
            'cert_airworthiness_no' => $brownCardNo,
        ]);

        $decoded = $raw ? json_decode($raw, true) : null;
        $success = ($decoded['status'] ?? '') === 'success';
        $msg     = $decoded['message'] ?? ($raw ?? 'No response from proxy');

        $record->update([
            'elitesuccess' => $success,
            'elitemsg'     => $msg,
        ]);

        Log::info('PostNIIPDataSlow: Elite browncard update', [
            'policyno'     => $policy->policyno,
            'browncard'    => $brownCardNo,
            'elite_status' => $success ? 'success' : 'failed',
            'elite_msg'    => $msg,
        ]);
    }
}
