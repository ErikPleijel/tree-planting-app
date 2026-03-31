<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MapMarkerService;
use App\Models\PlantingLocation;
use App\Models\Picture;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request, MapMarkerService $markerService)
    {
        // Collect filters for markers
        $filters = $request->only(['user_id', 'division_id', 'lga', 'status']);

        // Get all map markers (filtered)
        $markers = $markerService->getMarkers($filters);

        // Subquery: latest planting date per planting location
        $latestPlantings = DB::table('tree_plantings')
            ->select('planting_location_id', DB::raw('MAX(planting_date) as latest_planting_date'))
            ->groupBy('planting_location_id');

        // Latest 3 planting locations by latest planting activity
        $latestPlantingLocations = PlantingLocation::query()
            ->select('planting_locations.*')
            ->joinSub($latestPlantings, 'latest_plantings', function ($join) {
                $join->on('planting_locations.id', '=', 'latest_plantings.planting_location_id');
            })
            ->with(['division', 'treePlantings.treeType'])
            ->orderByDesc('latest_plantings.latest_planting_date')
            ->limit(6)
            ->get();

        $latestPictures = Picture::latest()
            ->take(10)
            ->get();

        return view('home', [
            'markers' => $markers,
            'latestPlantingLocations' => $latestPlantingLocations,
            'latestPictures' => $latestPictures,
        ]);

    }
}
