<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;
use App\Services\PhotoLocationMarkerService;

class PublicPlantingLocationController extends Controller
{
    public function show(string $public_code, PhotoLocationMarkerService $photoMarkerService)
    {
        $plantingLocation = PlantingLocation::with([
            'division',
            'status',
            'treePlantings' => function ($query) {
                $query->latest('planting_date');
            },
            'treePlantings.treeType',
            'pictures',
        ])
            ->where('public_code', $public_code)
            ->firstOrFail();

        $markers = [];

        if ($plantingLocation->latitude && $plantingLocation->longitude) {
            $markers[] = [
                'lat'   => $plantingLocation->latitude,
                'lng'   => $plantingLocation->longitude,
                'title' => $plantingLocation->location,
            ];
        }

        // Public since the decision recorded in DECISIONS.md: "Photo
        // capture-location markers now public on both pages" — a deliberate
        // reversal of Phase 4's original admin-only stance for captured
        // photo coordinates specifically (EXIF extraction/storage itself is
        // unchanged; only the display-scope decision moved).
        $photos = $photoMarkerService->build($plantingLocation);

        return view('public.planting-locations.show', compact('plantingLocation', 'markers', 'photos'));
    }
}
