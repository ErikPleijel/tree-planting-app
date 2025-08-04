<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Get all planting locations for the map
        $allLocations = PlantingLocation::with(['division', 'treePlantings'])->get();

        // Get paginated planting locations for the table with search
        $plantingLocations = PlantingLocation::with(['division', 'treePlantings'])
            ->when($search, function ($query) use ($search) {
                return $query->where('location', 'like', '%' . $search . '%');
            })
            ->paginate(25)
            ->withQueryString();

        // Create markers for the map
        $markers = $allLocations->map(function ($location) {
            $divisionName = $location->division ? $location->division->LGA_name : 'N/A';
            $totalTrees = $location->treePlantings->sum('number_of_trees');

            return [
                'id' => $location->id,
                'lat' => $location->latitude,
                'lng' => $location->longitude,
                'title' => $location->location,
                'markerType' => 'blue',
                'totalTrees' => $totalTrees,
                'popup' => "<div class='p-2'>
                    <h4 class='font-bold text-lg mb-2' style='color: #1e40af; display: block;'>{$location->location}</h4>
                    <p class='mb-1'><span class='font-medium'>LGA:</span> {$divisionName}</p>
                    <p class='mb-1'><span class='font-medium'>Total Trees:</span> {$totalTrees}</p>
                </div>"
            ];
        })->toArray();

        // Calculate statistics
        $totalLocations = $allLocations->count();
        $totalTrees = $allLocations->sum(function($location) {
            return $location->treePlantings->sum('number_of_trees');
        });

        return view('stats.map', [
            'markers' => $markers,
            'totalLocations' => $totalLocations,
            'totalTrees' => $totalTrees,
            'plantingLocations' => $plantingLocations,
            'search' => $search
        ]);
    }
}
