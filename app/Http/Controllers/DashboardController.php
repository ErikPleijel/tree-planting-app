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
        $treePlantings = TreePlanting::with(['plantingLocation.division', 'user', 'treeType', 'statusRelation'])
            ->where('user_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $isPrivileged = auth()->user()->hasAnyRole(['SuperAdmin', 'Admin', 'Monitor']);

        $inspections = $isPrivileged
            ? Inspection::with(['plantingLocation'])
                ->where('user_id', auth()->id())
                ->orderBy('updated_at', 'desc')
                ->paginate(20, ['*'], 'inspections_page')
            : null;

        $verifications = $isPrivileged
            ? TreePlanting::with(['plantingLocation', 'treeType', 'statusRelation'])
                ->where('status_updated_by', auth()->id())
                ->whereHas('statusRelation', fn($q) => $q->where('tree_planting_status', 'Verified'))
                ->orderBy('updated_at', 'desc')
                ->paginate(20, ['*'], 'verifications_page')
            : null;

        return view('dashboard', compact('treePlantings', 'inspections', 'verifications'));
    }
}
