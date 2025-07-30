<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MapMarkerService;

class HomeController extends Controller
{
    public function index(Request $request, MapMarkerService $markerService)
    {
        // Collect filters for markers
        $filters = $request->only(['user_id', 'division_id', 'lga', 'status']);

        // Get all map markers (filtered)
        $markers = $markerService->getMarkers($filters);

        // Pass markers to the dashboard view
        return view('home', compact('markers'));
    }
}
