<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessNiipManualImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NiipManualController extends Controller
{
    /**
     * Accept a spreadsheet of policies and hand it to the queue. Rows are posted to NIIP by
     * ProcessNiipManualImport (a web request cannot safely hold N x 180s HTTP calls).
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $path = $request->file('file')->store('niip-imports', 'local');

        Log::info('NIIP manual import queued', ['user_id' => Auth::id(), 'path' => $path]);

        ProcessNiipManualImport::dispatch($path);

        return back()->with('success', 'Spreadsheet uploaded. Rows are being sent to NIIP in the background - check the Queue Jobs page and the log for results.');
    }

    public function show()
    {
        return view('codes.niipmanual');
    }
}
