<?php

namespace App\Http\Controllers;

use App\Models\TreePlanting;
use Illuminate\Http\Request;
use App\Services\MapMarkerService;

class DashboardController extends Controller
{
    public function index(Request $request, MapMarkerService $markerService)
    {
        $treePlantings = TreePlanting::with(['plantingLocation.division', 'user', 'treeType', 'status'])
            ->where('user_id', auth()->id())
            ->orderBy('planting_date', 'desc')
            ->get();

        $markers = $treePlantings->map(function ($planting) {
            if ($planting->plantingLocation) {
                $status = $planting->statusRelation->tree_planting_status ?? '';
                $markerType = match($status) {
                    'Verified' => 'yellow',
                    'Planted' => 'green',
                    default => 'blue'
                };

                return [
                    'id' => $planting->id,
                    'lat' => $planting->plantingLocation->latitude,
                    'lng' => $planting->plantingLocation->longitude,
                    'title' => $planting->treeType->name ?? 'Tree',
                    'status' => $status,
                    'markerType' => $markerType,
                    'popup' => "<div class='p-2'>
                <h4 class='font-bold text-lg mb-2'>{$planting->treeType->name}</h4>
                <p class='mb-1'><span class='font-medium'>Location:</span> {$planting->plantingLocation->name}</p>
                <p class='mb-1'><span class='font-medium'>Status:</span> {$status}</p>
                <p class='mb-1'><span class='font-medium'>Date:</span> {$planting->planting_date}</p>
            </div>"
                ];
            }
            return null;
        })->filter()->toArray();



        $treePlantings = TreePlanting::with(['plantingLocation.division', 'user', 'treeType', 'statusRelation'])
            ->where('user_id', auth()->id())
            ->orderBy('planting_date', 'desc')
            ->paginate(10);


        return view('dashboard', compact('markers', 'treePlantings'));
    }
}
