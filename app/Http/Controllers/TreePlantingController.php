<?php

namespace App\Http\Controllers;

use App\Models\TreePlanting;
use App\Models\TreeType;
use Illuminate\Http\Request;

class TreePlantingController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        $query = \App\Models\TreePlanting::with(['plantingLocation', 'treeType', 'statusRelation'])
            ->join('tree_types', 'tree_plantings.tree_type_id', '=', 'tree_types.id')
            ->join('planting_locations', 'tree_plantings.planting_location_id', '=', 'planting_locations.id')
            ->select('tree_plantings.*');

        if ($treeTypeId = $request->input('tree_type_id')) {
            $query->where('tree_plantings.tree_type_id', $treeTypeId);
        }

        if ($status = $request->input('status')) {
            $query->where('tree_plantings.status', $status);
        }

        match ($request->input('sort', 'date_asc')) {
            'date_desc'      => $query->orderBy('tree_plantings.planting_date', 'desc'),
            'tree_type_asc'  => $query->orderBy('tree_types.name', 'asc'),
            'tree_type_desc' => $query->orderBy('tree_types.name', 'desc'),
            'location_asc'   => $query->orderBy('planting_locations.location', 'asc'),
            'location_desc'  => $query->orderBy('planting_locations.location', 'desc'),
            default          => $query->orderBy('tree_plantings.planting_date', 'asc'),
        };

        $treePlantings = $query->paginate(15)->withQueryString();
        $treeTypes = TreeType::orderBy('name')->get();
        $statuses = \App\Models\TreePlantingStatus::all();

        return view('tree-plantings.index', compact('treePlantings', 'treeTypes', 'statuses'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $plantingLocationId = $request->get('planting_location_id');
        $location = \App\Models\PlantingLocation::findOrFail($plantingLocationId);

        return view('tree-plantings.create', [
            'location' => $location,
            'treeTypes' => \App\Models\TreeType::orderBy('name')->get(),
            'statuses' => \App\Models\TreePlantingStatus::all(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'planting_date' => 'required|date',
            'number_of_trees' => 'required|integer|min:1',
            'tree_type_id' => 'required|exists:tree_types,id',
            'planting_location_id' => 'required|exists:planting_locations,id',
            'status' => 'required|exists:tree_planting_status,id',
        ]);

        $validated['user_id'] = auth()->id();

        // Set default status if user doesn't have required role
        if (!auth()->user()->hasRole(['Admin', 'SuperAdmin', 'Monitor'])) {
            $validated['status'] = 1;
        }

        \App\Models\TreePlanting::create($validated);

        return redirect()
            ->route('planting-locations.show', $validated['planting_location_id'])
            ->with('success', 'Tree planting added successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(TreePlanting $treePlanting)
    {

        $treePlanting->load(['plantingLocation', 'treeType', 'status']);

        return view('tree-plantings.show', compact('treePlanting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\TreePlanting $treePlanting)
    {
        $treePlanting->load(['plantingLocation.division', 'division']);

        return view('tree-plantings.edit', [
            'treePlanting' => $treePlanting,
            'locations' => \App\Models\PlantingLocation::all(),
            'treeTypes' => \App\Models\TreeType::all(),
            'statuses' => \App\Models\TreePlantingStatus::all(),
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(\Illuminate\Http\Request $request, \App\Models\TreePlanting $treePlanting)
    {
        $validated = $request->validate([
            'planting_date' => 'required|date',
            'number_of_trees' => 'required|integer|min:1',
            'tree_type_id' => 'required|exists:tree_types,id',
            'planting_location_id' => 'required|exists:planting_locations,id',
            'status' => 'required|exists:tree_planting_status,id',
        ]);

        // Preserve existing status if user doesn't have required role
        if (!auth()->user()->hasRole(['Admin', 'SuperAdmin', 'Monitor'])) {
            $validated['status'] = $treePlanting->status;
        }

        $treePlanting->update($validated);

        return redirect()
            ->route('planting-locations.show', $treePlanting->planting_location_id)
            ->with('success', 'Tree planting updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TreePlanting $treePlanting)
    {
        $locationId = $treePlanting->planting_location_id;
        $treePlanting->delete();

        return redirect()
            ->route('planting-locations.show', $locationId)
            ->with('success', 'Tree planting deleted successfully');
    }

    public function report(Request $request)
    {
        $query = \App\Models\TreePlanting::with(['treeType', 'plantingLocation.division', 'user', 'statusRelation'])
            ->select('tree_plantings.*');

        // Filtering
        $query->when($request->input('tree_type_id'), fn($q, $id) => $q->where('tree_type_id', $id));
        $query->when($request->input('division_id'), fn($q, $id) =>
            $q->whereHas('plantingLocation', fn($q2) => $q2->where('division_id', $id))
        );
        $query->when($request->input('user_id'), fn($q, $id) => $q->where('user_id', $id));
        $query->when($request->input('status'), fn($q, $status) => $q->where('status', $status));

        // Search (on location)
        if ($search = $request->input('search')) {
            $query->whereHas('plantingLocation', fn($q2) =>
                $q2->where('location', 'like', "%$search%")
            );
        }

        // Sorting (default: planting date desc)
        $sortField = $request->input('sort', 'planting_date');
        $sortDir = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDir);

        // Pagination (25 per page, preserve filters)
        $treePlantings = $query->paginate(25)->withQueryString();

        // Dropdown data for filters
        $treeTypes = TreeType::orderByDesc('name')->get();
        $divisions = \App\Models\Division::all();
        $users = \App\Models\User::all();
        $statuses = \App\Models\TreePlantingStatus::all();

        return view('tree-plantings.report', compact(
            'treePlantings', 'treeTypes', 'divisions', 'users', 'statuses'
        ));
    }


}
