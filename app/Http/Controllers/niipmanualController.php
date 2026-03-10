<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class NiipManualController extends Controller
{
    public function uploadExcel(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);

    // Store uploaded file
    $path = $request->file('file')->store('uploads');

    // Process the file
    $this->processExcel(storage_path('app/private/' . $path));

    return back()->with('success', 'Excel uploaded and processed successfully.');
}



    public function processExcel($filePath)
    {
        // Load the Excel file
        $rows = Excel::toArray([], $filePath);
                // Use the first sheet
        $sheet = $rows[0];


        foreach ($sheet as $index => $row) {

            // Skip header row
            if ($index === 0) {
                continue;
            }

            // Safely parse IssueDate
            try {
                $excelValue = $row[3];   // e.g. 45293.87917

$phpDate = Date::excelToDateTimeObject($excelValue);

$date = Carbon::instance($phpDate);

            } catch (\Exception $e) {
                Log::error("Invalid date format on row {$index}: {$row[3]}");
                continue;
            }

            // Build payload
            $payload = [
                "APIKey" => config('variables.NIIP_API_KEY'),
                "Purpose" => 3,
                "VehicleColor" => 14,
                "VehicleMake" => $row[18] ?? null,
                "VehicleModel" => $row[20] ?? null,
                "EngineCap" => 3,
                "State" => $row[15] ?? null,
                "LGA" => $row[13] ?? null,
                "RegNo" => $row[9] ?? null,
                "ChassisNo" => $row[10] ?? null,
                "EngineNo" => $row[11] ?? null,
                "PolicyHolderFirstName" => $row[6] ?? null,
                "PolicyHolderLastName" => $row[7] ?? null,
                "PolicyHolderMiddleName" => $row[8] ?? null,
                "PolicyHolderMobileNo" => $row[5] ?? '08065657291',
                "PolicyHolderEmail" => $row[4] ?? 'info@salamtakafulinsurance.com',
                "PolicyHolderNIN" => '',
                "IssueDate" => $date->format('Y-m-d'),
                "PolicyHolderAddress" => trim($row[2] ?? ''),
                "PolicyNumber" => $row[1] ?? null,
            ];

            // Make API call with error handling
            try {
                $niipdatajSon=json_encode($payload);
                $response = Http::withBody($niipdatajSon)->timeout(180)->post(config('variables.NIIP_URL'));

                if ($response->failed()) {
                    Log::error("API failed for row {$index}", [
                        'payload' => $payload,
                        'response' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("HTTP error on row {$index}: " . $e->getMessage());
            }
        }

        return "Excel processed successfully.";
    }
    public function show()
    {
        return view('codes.niipmanual');
    }
}