<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Posts each row of an uploaded policy spreadsheet to NIIP, then deletes the upload.
 * Logs row numbers and outcomes only - never the policyholder payload (PII).
 */
class ProcessNiipManualImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 3600;

    public function __construct(public string $path) {}

    public function handle(): void
    {
        $disk = Storage::disk('local');

        if (!$disk->exists($this->path)) {
            Log::error('NIIP manual import: file missing', ['path' => $this->path]);
            return;
        }

        try {
            $sheet = Excel::toArray([], $disk->path($this->path))[0] ?? [];
            $sent = $skipped = $failed = 0;

            foreach ($sheet as $index => $row) {
                if ($index === 0) {
                    continue; // header
                }

                $payload = $this->buildPayload($row);
                if ($payload === null) {
                    $skipped++;
                    Log::warning('NIIP manual import: row skipped (missing policy no / reg no / valid date)', ['row' => $index]);
                    continue;
                }

                try {
                    $response = Http::withBody(json_encode($payload))->timeout(180)->post(config('variables.NIIP_URL'));

                    if ($response->failed()) {
                        $failed++;
                        Log::error('NIIP manual import: API failed', ['row' => $index, 'status' => $response->status(), 'body' => mb_substr($response->body(), 0, 500)]);
                    } else {
                        $sent++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('NIIP manual import: HTTP error', ['row' => $index, 'error' => $e->getMessage()]);
                }
            }

            Log::info('NIIP manual import finished', ['path' => $this->path, 'sent' => $sent, 'skipped' => $skipped, 'failed' => $failed]);
        } finally {
            $disk->delete($this->path);
        }
    }

    private function buildPayload(array $row): ?array
    {
        $policyNo = trim((string) ($row[1] ?? ''));
        $regNo    = trim((string) ($row[9] ?? ''));

        if ($policyNo === '' || $regNo === '') {
            return null;
        }

        try {
            $date = Carbon::instance(Date::excelToDateTimeObject($row[3] ?? null));
        } catch (\Throwable) {
            return null;
        }

        return [
            "APIKey" => config('variables.NIIP_API_KEY'),
            "Purpose" => 3,
            "VehicleColor" => 14,
            "VehicleMake" => $row[18] ?? null,
            "VehicleModel" => $row[20] ?? null,
            "EngineCap" => 3,
            "State" => $row[15] ?? null,
            "LGA" => $row[13] ?? null,
            "RegNo" => $regNo,
            "ChassisNo" => $row[10] ?? null,
            "EngineNo" => $row[11] ?? null,
            "PolicyHolderFirstName" => $row[6] ?? null,
            "PolicyHolderLastName" => $row[7] ?? null,
            "PolicyHolderMiddleName" => $row[8] ?? null,
            "PolicyHolderMobileNo" => $row[5] ?? '08065657291',
            "PolicyHolderEmail" => $row[4] ?? 'info@salamtakafulinsurance.com',
            "PolicyHolderNIN" => '',
            "IssueDate" => $date->format('Y-m-d'),
            "PolicyHolderAddress" => trim((string) ($row[2] ?? '')),
            "PolicyNumber" => $policyNo,
        ];
    }
}
