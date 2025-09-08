<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\PlantingLocation;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with(['user', 'plantingLocation']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('comment', 'LIKE', "%{$search}%")
                  ->orWhereHas('plantingLocation', function($q) use ($search) {
                      $q->where('location', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'verified') {
                $query->where('verified', true);
            } elseif ($status === 'unverified') {
                $query->where('verified', false);
            }
        }

        $inspections = $query->orderBy('inspection_date', 'desc')
            ->paginate(15)
            ->withQueryString(); // Preserve query parameters in pagination links

        return view('inspections.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        $plantingLocation = PlantingLocation::findOrFail($request->planting_location_id);
        return view('inspections.create', compact('plantingLocation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'comment' => 'nullable|string',
            'verified' => 'required|boolean',
            'planting_location_id' => 'required|exists:planting_locations,id',
        ]);

        $validated['user_id'] = auth()->id();

        Inspection::create($validated);

        return redirect()->route('planting-locations.show', $validated['planting_location_id'])
            ->with('success', 'Inspection recorded successfully.');
    }

    public function edit(Inspection $inspection)
    {
        $plantingLocations = PlantingLocation::all();
        return view('inspections.edit', compact('inspection', 'plantingLocations'));
    }


    public function update(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'comment' => 'nullable|string',
            'verified' => 'required|boolean',
            'planting_location_id' => 'required|exists:planting_locations,id',
        ]);

        $inspection->update($validated);

        return redirect()->route('planting-locations.show', $inspection->planting_location_id)
            ->with('success', 'Inspection updated successfully.');
    }

    public function destroy(Inspection $inspection)
    {
        if (!in_array(auth()->user()->role->name, ['Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $locationId = $inspection->planting_location_id;
        $inspection->delete();

        return redirect()->route('planting-locations.show', $locationId)
            ->with('success', 'Inspection deleted successfully.');
    }
}
