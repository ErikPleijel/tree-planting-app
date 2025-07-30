<?php

namespace App\Http\Controllers;

use App\Models\TreePlanting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $treePlantings = TreePlanting::with(['plantingLocation.division', 'user', 'treeType', 'statusRelation'])
            ->where('user_id', auth()->id())
            ->orderBy('planting_date', 'desc')
            ->paginate(10);

        return view('dashboard', compact('treePlantings'));
    }
}
