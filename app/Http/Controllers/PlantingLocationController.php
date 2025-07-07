<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use Illuminate\Http\Request;

class PlantingLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plantingLocations = \App\Models\PlantingLocation::with(['division', 'status'])->get();

        return view('planting-locations.index', compact('plantingLocations'));
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
    public function show(PlantingLocation $plantingLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlantingLocation $plantingLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PlantingLocation $plantingLocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlantingLocation $plantingLocation)
    {
        //
    }
}
