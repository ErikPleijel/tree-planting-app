<?php

namespace App\Http\Controllers;

use App\Models\TreePlanting;
use Illuminate\Http\Request;

class TreePlantingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $treePlantings = \App\Models\TreePlanting::with(['plantingLocation', 'treeType', 'status','statusRelation'])->get();
     //   $treePlantings = TreePlanting::with(['statusRelation', 'treeType', 'plantingLocation'])->get();

        return view('tree-plantings.index', compact('treePlantings'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tree-plantings.create', [
            'locations' => \App\Models\PlantingLocation::all(),
            'treeTypes' => \App\Models\TreeType::all(),
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

        $treePlanting->update($validated);

        return redirect()
        ->route('planting-locations.show', $treePlanting->planting_location_id)
        ->with('success', 'Tree planting updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\TreePlanting $treePlanting)
    {

        if (!in_array(auth()->user()->role->name, ['Admin'])) {
            abort(403, 'Unauthorized.');
        }
        $treePlanting->delete();

        return redirect()->route('tree-plantings.index')
            ->with('success', 'Tree Planting deleted.');
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
        $treeTypes = \App\Models\TreeType::all();
        $divisions = \App\Models\Division::all();
        $users = \App\Models\User::all();
        $statuses = \App\Models\TreePlantingStatus::all();

        return view('tree-plantings.report', compact(
            'treePlantings', 'treeTypes', 'divisions', 'users', 'statuses'
        ));
    }


}
