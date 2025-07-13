<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MapMarkerService;

class DashboardController extends Controller
{
    public function index(Request $request, MapMarkerService $markerService)
    {
        $filters = $request->only(['user_id', 'division_id', 'lga', 'status']);

        $markers = $markerService->getMarkers($filters);

        return view('dashboard', compact('markers'));
    }
}
