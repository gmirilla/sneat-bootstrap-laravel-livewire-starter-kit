<?php

namespace App\Http\Controllers;

use App\Imports\vehiclecolorImport;
use App\Models\vehiclecolor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class VehiclecolorController extends Controller
{
        public function importvcolor(Request $request)
    {

        $request->validate([
            'vcolorimport' => 'required|max:2048|mimes:xlsx,xls,csv'
        ]);

       

        Excel::import(new vehiclecolorImport, $request->file('vcolorimport'));

        return back()->with('success', 'Vehicle Colors imported successfully.');
    }

    public function getColors()
    {



        $response = Http::get('http://niip.ng/api/getColors/'); // Make the API request
$data = $response->json(); // Decode JSON response

echo $response;
// Now you can access the data
dd($data); // Dump and die to inspect the decoded JSON

        return response()->json(vehiclecolor::where('niipvcolorid', $make)->orderBy('vcolorname')->get());
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $colors = vehiclecolor::all();
        return view('codes.vehiclecolor', compact('colors'));
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
    public function show(vehiclecolor $vehiclecolor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(vehiclecolor $vehiclecolor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, vehiclecolor $vehiclecolor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(vehiclecolor $vehiclecolor)
    {
        //
    }
}
