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
    public function index(Request $request)
{
    $query = PlantingLocation::with(['division', 'statusRelation',
        'treePlantings' => function($query) {
            $query->orderBy('planting_date', 'desc');
        },
        'treePlantings.treeType',
        'treePlantings.statusRelation'
    ]);

    // Apply division filter
    if ($request->filled('division')) {
        $query->where('division_id', $request->division);
    }

    // Apply search filter
    if ($request->filled('search')) {
        $query->where('location', 'like', '%' . $request->search . '%');
    }

    $plantingLocations = $query
        ->withSum('treePlantings as total_trees', 'number_of_trees')
        ->paginate(20)
        ->withQueryString(); // This preserves the filters in pagination links

    // Get divisions for the dropdown
    $divisions = \App\Models\Division::orderBy('LGA_name')->get();

    return view('planting-locations.index', compact('plantingLocations', 'divisions'));
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
    public function store(Request $request)
{
    $validated = $request->validate([
        'location' => 'required|string|max:255',
        'division_id' => 'required|exists:division,id',
        'status' => 'required|exists:planting_location_status,id', // Add this line
        'comment' => 'nullable|string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
    ]);

    $validated['user_id'] = auth()->id();

    // Create the planting location only once
    $plantingLocation = PlantingLocation::create($validated);

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
        $plantingLocation->load(['division', 'pictures', 'statusRelation', 'treePlantings.treeType', 'treePlantings.statusRelation']);


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
    public function update(Request $request, PlantingLocation $plantingLocation)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'division_id' => 'required|exists:division,id',
            'comment' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $plantingLocation->update($validated);

        return redirect()->route('planting-locations.show', $plantingLocation)
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
