<?php

namespace App\Http\Controllers;

use App\Models\vehicleMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\vehicleMakeImport;
use App\Models\vehicleModel;
use Illuminate\Http\Client\Pool;


class VehicleMakeController extends Controller
{
    public function importvmake(Request $request)
    {

        $request->validate([
            'vmakeimport' => 'required|max:2048|mimes:xlsx,xls,csv'
        ]);



        Excel::import(new vehicleMakeImport, $request->file('vmakeimport'));

        return back()->with('success', 'Vehicle Makes imported successfully.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $vmakes = vehicleMake::all();

        return view('codes.vehiclemakes', compact('vmakes'));
    }

    public function updatevmake()
    {
        $response = Http::get('https://niip.ng/api/getMake/');

        if (!$response->successful()) {
            return back()->with('error', 'Failed to fetch vehicle makes.');
        }

        $makes = $response->json();

        // Prepare arrays for bulk upsert
        $makeData = [];
        $modelRequests = [];

        // Build make list + prepare async model requests
        foreach ($makes as $item) {
            if ($item['code'] != '0') {
                $makeData[] = [
                    'niipvmid' => $item['code'],
                    'vmake' => $item['name']
                ];

                // Prepare async request for each model
                $modelRequests[$item['code']] = fn() =>
                Http::get("https://niip.ng/api/getModel/{$item['code']}");
            }
        }

        // Bulk upsert makes
        vehicleMake::upsert($makeData, ['niipvmid'], ['vmake']);

        // Fetch all models concurrently
        $modelResponses = Http::pool(function (Pool $pool) use ($makes) {
    $requests = [];

    foreach ($makes as $item) {
        if ($item['code'] != '0') {
            $requests[$item['code']] = $pool->get("https://niip.ng/api/getModel/{$item['code']}");
        }
    }

    return $requests;
});


        $modelData = [];

        foreach ($modelResponses as $makeId => $res) {
            if ($res->successful()) {
                foreach ($res->json() as $model) {
                    if ($model['code'] != '0') {
                        $modelData[] = [
                            'vmodelid' => $model['code'],
                            'vmodelname' => $model['name'],
                            'niipvmid' => $makeId,
                            'vmodelfullname' => $makes[array_search($makeId, array_column($makes, 'code'))]['name']
                                . ' ' . $model['name']
                        ];
                    }
                }
            }
        }

        // Bulk upsert models
        vehicleModel::upsert($modelData, ['vmodelid'], ['vmodelname', 'niipvmid', 'vmodelfullname']);

        return back()->with('success', 'Vehicle makes and models updated successfully.');
    }


    public function getModels($make)
    {

        return response()->json(vehicleModel::where('niipvmid', $make)->orderBy('vmodelname')->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(vehicleMake $vehicleMake)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(vehicleMake $vehicleMake)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, vehicleMake $vehicleMake)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(vehicleMake $vehicleMake)
    {
        //
    }
}
