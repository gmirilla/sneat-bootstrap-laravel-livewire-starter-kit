<?php

namespace App\Http\Controllers;

use App\Models\nin;
use Illuminate\Http\Request;

class NinController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        return view('ninverification.begin');
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
    public function show(nin $nin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(nin $nin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, nin $nin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(nin $nin)
    {
        //
    }
}
