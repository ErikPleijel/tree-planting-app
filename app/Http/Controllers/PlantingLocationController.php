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
        $query = PlantingLocation::with([
            'division',
            'status',
            'treePlantings' => function ($query) {
                $query->orderBy('planting_date', 'desc');
            },
            'treePlantings.treeType',
            'treePlantings.statusRelation',
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
            ->withQueryString();

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
            'location'     => 'required|string|max:255',
            'division_id'  => 'required|exists:division,id',
            'status_id'    => 'required|exists:planting_location_status,id',
            'comment'      => 'nullable|string',
            'contributors' => 'nullable|string',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
        ]);

        $validated['user_id']      = auth()->id();
        $validated['contributors'] = $request->contributors
            ? strip_tags($request->contributors, '<p><br><strong><em><u><ol><ul><li><a><span>')
            : null;

        $plantingLocation = PlantingLocation::create($validated);

        return redirect()
            ->route('planting-locations.show', $plantingLocation->id)
            ->with('success', 'Planting location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(
        Request $request,
        MapMarkerService $markerService,
        PlantingLocation $plantingLocation
    ) {
        $filters = [
            'id' => $plantingLocation->id,
        ];

        $markers = $markerService->getMarkers($filters);

        $plantingLocation->load([
            'division',
            'pictures',
            'status',
            'treePlantings.treeType',
            'treePlantings.statusRelation',
        ]);

        return view('planting-locations.show', compact('plantingLocation', 'markers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlantingLocation $plantingLocation)
    {
        return view('planting-locations.edit', [
            'plantingLocation' => $plantingLocation,
            'divisions'        => \App\Models\Division::all(),
            'statuses'         => \App\Models\PlantingLocationStatus::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PlantingLocation $plantingLocation)
    {
        $validated = $request->validate([
            'location'     => 'required|string|max:255',
            'division_id'  => 'required|exists:division,id',
            'status_id'    => 'required|exists:planting_location_status,id',
            'comment'      => 'nullable|string',
            'contributors' => 'nullable|string',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
        ]);

        $validated['contributors'] = $request->contributors
            ? strip_tags($request->contributors, '<p><br><strong><em><u><ol><ul><li><a><span>')
            : null;

        $plantingLocation->update($validated);

        return redirect()
            ->route('planting-locations.show', $plantingLocation)
            ->with('success', 'Planting Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlantingLocation $plantingLocation)
    {
        if (auth()->user()->role->name !== 'Admin') {
            abort(403, 'Only admins can delete locations.');
        }

        $plantingLocation->delete();

        return redirect()
            ->route('planting-locations.index')
            ->with('success', 'Planting Location deleted.');
    }

    public function qrLabel(PlantingLocation $plantingLocation)
    {
        return view('planting-locations.qr-label', compact('plantingLocation'));
    }
}
