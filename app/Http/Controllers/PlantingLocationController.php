<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use Illuminate\Http\Request;

use App\Services\MapMarkerService;

class PlantingLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //$plantingLocations = \App\Models\PlantingLocation::with(['division', 'status'])->get();

        $plantingLocations = PlantingLocation::with(['division', 'statusRelation'])
            ->withSum('treePlantings as total_trees', 'number_of_trees')
            ->get();

        return view('planting-locations.index', compact('plantingLocations'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('planting-locations.create', [
            'divisions' => \App\Models\Division::all(),
            'statuses' => \App\Models\PlantingLocationStatus::all(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'division_id' => 'required|exists:division,id',
            'comment' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|exists:planting_location_status,id',
        ]);

        $validated['user_id'] = auth()->id();

        \App\Models\PlantingLocation::create($validated);

        $plantingLocation = \App\Models\PlantingLocation::create($validated);

        return redirect()
            ->route('planting-locations.show', $plantingLocation->id)
            ->with('success', 'Planting location created successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, MapMarkerService $markerService, \App\Models\PlantingLocation $plantingLocation)
    {
        $filters = [
            'id' => $plantingLocation->id,
        ];

        $markers = $markerService->getMarkers($filters);

     //   $plantingLocation->load(['division', 'statusRelation']);
        $plantingLocation->load(['division', 'statusRelation', 'treePlantings.treeType', 'treePlantings.statusRelation']);


        return view('planting-locations.show', compact('plantingLocation', 'markers'));
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\PlantingLocation $plantingLocation)
    {
        return view('planting-locations.edit', [
            'plantingLocation' => $plantingLocation,
            'divisions' => \App\Models\Division::all(),
            'statuses' => \App\Models\PlantingLocationStatus::all(),
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(\Illuminate\Http\Request $request, \App\Models\PlantingLocation $plantingLocation)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'division_id' => 'required|exists:division,id',
            'comment' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|exists:planting_location_status,id',
        ]);

        $plantingLocation->update($validated);

        return redirect()->route('planting-locations.index')
            ->with('success', 'Planting Location updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\PlantingLocation $plantingLocation)
    {
        if (auth()->user()->role->name !== 'Admin') {
            abort(403, 'Only admins can delete locations.');
        }

        $plantingLocation->delete();

        return redirect()->route('planting-locations.index')
            ->with('success', 'Planting Location deleted.');
    }

}
