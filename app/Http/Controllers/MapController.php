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
                'popup' => "<div class='p-3 min-w-[200px]'>
                    <div class='mb-2'>
                        <h4 class='font-bold text-lg leading-none mb-0 text-blue-800'>{$location->location}</h4>
                        <p class='text-xs mb-0 text-gray-500' style='margin-top: 2px;'>" . implode(' ', str_split($location->public_code, 3)) . "</p>
                    </div>
                    <p class='text-lg mb-1'><span class='font-medium'>👉</span> {$divisionName}</p>
                    <p class='text-sm mb-1'><span class='font-medium'>Total Trees:</span> {$totalTrees}</p>
                    <div class='flex gap-3 mt-2'>
                        <a href='" . route('public.planting-locations.show', $location->public_code) . "'
                           class='text-blue-600 hover:underline text-sm font-medium'>
                            View details →
                        </a>
                        <a href='" . route('planting-locations.show', $location->id) . "'
                           class='text-green-700 hover:underline text-sm font-medium'>
                            Edit Location Data →
                        </a>
                    </div>
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
