<?php

namespace App\Http\Controllers;

use App\Imports\stateImport;
use App\Models\states;
use App\Models\lga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class StatesController extends Controller
{

    public function importstates(Request $request)
    {

        $request->validate([
            'stateimport' => 'required|max:2048|mimes:xlsx,xls,csv'
        ]);



        Excel::import(new stateImport, $request->file('stateimport'));

        return back()->with('success', 'States imported successfully.');
    }

    public function updatestates()
    {
        $response = Http::get('https://niip.ng/api/getState/'); // Make the API request
        $data = $response->json(); // Decode JSON response



        if ($response->successful()) {
            $data = json_decode($response->body(), true);

            foreach ($data as $item) {
                // Assuming the structure of each item in the response
                if ($item['code'] != '0') {
                    states::updateOrCreate(
                        ['stateid' => $item['code']], // Unique identifier
                        [
                            'statename' => $item['name'],
                            // Add other fields as necessary
                        ]);
                        
                        //update or create the LGA record for each state
                        $responselga = Http::get('https://niip.ng/api/getState/'.$item['code']); // Make the API request
                        if ($responselga->successful()) {
                            $datalga = json_decode($responselga->body(), true);
            
                            foreach ($datalga as $lgaitem) {
                                // Assuming the structure of each item in the response
                                if ($lgaitem['code'] != '0') {
                                    lga::updateOrCreate(
                                        ['lgaid' => $lgaitem['code']], // Unique identifier
                                        [
                                            'lganame' => $lgaitem['name'],
                                            'stateid' => $item['code'],
                                            // Add other fields as necessary
                                        ]);
                                }
                            }
                }
            }
        }


        return back()->with('success', 'States updated successfully.');
    }
}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $states = states::all();
        return view('codes.states', compact('states'));
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
    public function show(states $states)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(states $states)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, states $states)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(states $states)
    {
        //
    }
}
