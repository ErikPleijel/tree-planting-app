<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\TreePlanting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $treePlantings = TreePlanting::with(['plantingLocation.division', 'user', 'treeType', 'statusRelation'])
            ->where('user_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $inspections = auth()->user()->hasAnyRole(['SuperAdmin', 'Admin', 'Monitor'])
            ? Inspection::with(['plantingLocation'])
                ->where('user_id', auth()->id())
                ->orderBy('updated_at', 'desc')
                ->paginate(10, ['*'], 'inspections_page')
            : null;

        return view('dashboard', compact('treePlantings', 'inspections'));
    }
}
