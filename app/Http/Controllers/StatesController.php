<?php

namespace App\Http\Controllers;

use App\Imports\stateImport;
use App\Models\states;
use Illuminate\Http\Request;
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
