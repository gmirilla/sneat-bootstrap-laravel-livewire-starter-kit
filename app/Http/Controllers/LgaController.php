<?php

namespace App\Http\Controllers;

use App\Imports\lgaImport;
use App\Models\lga;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LgaController extends Controller
{
    public function importlga(Request $request)
    {
        $request->validate([
            'lgaimport' => 'required|max:2048|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new lgaImport, $request->file('lgaimport'));

        return back()->with('success', 'LGAs imported successfully.');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $lgas = lga::all();
        return view('codes.lga', compact('lgas'));
    }


        public function getlgas($state)
{
    $lgas = lga::where('stateid', $state)->orderBy('lganame')->get();
   

    return response()->json(lga::where('stateid', $state)->orderBy('lganame')->get());
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
    public function show(lga $lga)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(lga $lga)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, lga $lga)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(lga $lga)
    {
        //
    }
}
