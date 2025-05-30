<?php

namespace App\Http\Controllers;

use App\Imports\niipvehicleuseImport;
use App\Models\niipvehicleuse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NiipvehicleuseController extends Controller
{
    public function importvuse(Request $request)
    {

        $request->validate([
            'vuseimport' => 'required|max:2048|mimes:xlsx,xls,csv'
        ]);

       

        Excel::import(new niipvehicleuseImport, $request->file('vuseimport'));

        return back()->with('success', 'Vehicle Uses imported successfully.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $vuses = niipvehicleuse::all();
        return view('codes.niipvehicleuse', compact('vuses'));
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
    public function show(niipvehicleuse $niipvehicleuse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(niipvehicleuse $niipvehicleuse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, niipvehicleuse $niipvehicleuse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(niipvehicleuse $niipvehicleuse)
    {
        //
    }
}
