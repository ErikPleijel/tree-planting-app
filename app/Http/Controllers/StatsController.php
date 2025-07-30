<?php

namespace App\Http\Controllers;

use App\Models\TreePlanting;
use App\Models\TreeType;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function stats1()
    {
        $lgaStats = TreePlanting::select('division.LGA_name as lga', DB::raw('SUM(tree_plantings.number_of_trees) as total_trees'))
            ->join('planting_locations', 'tree_plantings.planting_location_id', '=', 'planting_locations.id')
            ->join('division', 'planting_locations.division_id', '=', 'division.id')
            ->groupBy('division.LGA_name')
            ->orderBy('total_trees', 'desc')
            ->get();

        $treeTypeStats = TreePlanting::select('tree_types.name as tree_type', DB::raw('SUM(tree_plantings.number_of_trees) as total_trees'))
            ->join('tree_types', 'tree_plantings.tree_type_id', '=', 'tree_types.id')
            ->groupBy('tree_types.name')
            ->orderBy('total_trees', 'desc')
            ->get();

        return view('stats.stats1', compact('lgaStats', 'treeTypeStats')); // Changed from 'stats1' to 'stats.stats1'
    }
}
