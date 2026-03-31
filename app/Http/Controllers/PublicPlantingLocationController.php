<?php

namespace App\Http\Controllers;

use App\Models\PlantingLocation;

class PublicPlantingLocationController extends Controller
{
    public function show(string $public_code)
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

        return view('public.planting-locations.show', compact('plantingLocation', 'markers'));
    }
}
