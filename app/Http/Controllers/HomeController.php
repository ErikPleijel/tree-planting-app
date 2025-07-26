<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MapMarkerService;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request, MapMarkerService $markerService)
    {
        // Collect filters for markers
        $filters = $request->only(['user_id', 'division_id', 'lga', 'status']);

        // Get all map markers (filtered)
        $markers = $markerService->getMarkers($filters);

        // Get tree planting stats by tree type
        $treeStats = DB::select("
            SELECT
                IFNULL(tree_types.name, 'Unknown') AS type_name,
                SUM(tree_plantings.number_of_trees) AS total
            FROM tree_plantings
            LEFT JOIN tree_types ON tree_plantings.tree_type_id = tree_types.id
            GROUP BY tree_types.name
            ORDER BY total DESC
        ");

        // Pass both markers and tree stats to the dashboard view
        return view('home', compact('markers', 'treeStats'));
    }
}
