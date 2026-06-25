<?php

namespace App\Jobs;

use App\Models\ecmr;
use App\Models\policy;
use App\Services\EcmrService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckEcmrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $regno,
        private int $policyId
    ) {}

    public function handle(EcmrService $ecmrService): void
    {
        $pol = policy::find($this->policyId);

        if (!$pol) {
            Log::warning('CheckEcmrJob: policy not found', ['policyId' => $this->policyId]);
            return;
        }

        if (!str_contains(strtolower($pol->producttype ?? ''), 'motor')) {
            return;
        }

        // Skip if a successful check exists within the last year;
        // a check_failed record does not count so retries are still attempted.
        $recentExists = ecmr::where('licence_plate', $this->regno)
            ->where('created_at', '>=', now()->subYear())
            ->where('status', '!=', 'check_failed')
            ->exists();

        if ($recentExists) {
            Log::info('CheckEcmrJob: skipping, recent ECMR record exists', ['regno' => $this->regno]);
            return;
        }

        $ecmrService->check($this->regno, $this->policyId);
    }
}
