<?php

namespace App\Jobs;

use App\Models\policy;
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
}
