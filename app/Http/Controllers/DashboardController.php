<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\TreePlanting;
use App\Models\TreePlantingStatus;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $treePlantings = TreePlanting::with(['plantingLocation.division', 'user', 'treeType', 'statusRelation'])
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $isPrivileged = auth()->user()->hasAnyRole(['SuperAdmin', 'Admin', 'Monitor']);

        $inspections = $isPrivileged
            ? Inspection::with(['plantingLocation'])
                ->where('user_id', $userId)
                ->orderBy('updated_at', 'desc')
                ->paginate(20, ['*'], 'inspections_page')
            : null;

        $verifications = $isPrivileged
            ? TreePlanting::with(['plantingLocation', 'treeType', 'statusRelation'])
                ->where('status_updated_by', $userId)
                ->whereHas('statusRelation', fn($q) => $q->where('tree_planting_status', 'Verified'))
                ->orderBy('updated_at', 'desc')
                ->paginate(20, ['*'], 'verifications_page')
            : null;

        $statTreesPlanted   = TreePlanting::where('user_id', $userId)->sum('number_of_trees');
        $statVerifications  = $isPrivileged
            ? TreePlanting::where('status_updated_by', $userId)
                ->whereHas('statusRelation', fn($q) => $q->where('tree_planting_status', 'Verified'))
                ->count()
            : null;
        $statInspections    = $isPrivileged
            ? Inspection::where('user_id', $userId)->count()
            : null;

        return view('dashboard', compact(
            'treePlantings', 'inspections', 'verifications',
            'statTreesPlanted', 'statVerifications', 'statInspections'
        ));
    }
}
