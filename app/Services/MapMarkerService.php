<?php

namespace App\Services;

use App\Models\PlantingLocation;

class MapMarkerService
{
    public function getMarkers(array $filters = []): array
    {
        $query = PlantingLocation::with(['division', 'treePlantings']);

        if (!empty($filters['lga'])) {
            $query->where('location', 'LIKE', '%' . $filters['lga'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (!empty($filters['division_id'])) {
            $query->where('division_id', $filters['division_id']);
        }

        return $query->get()
            ->filter(function ($location) {
                return !is_null($location->latitude) && !is_null($location->longitude);
            })
            ->map(function ($location) {
                $divisionName = $location->division ? $location->division->LGA_name : 'N/A';
                $totalTrees = $location->treePlantings->sum('number_of_trees');

                return [
                    'id' => $location->id,
                    'lat' => $location->latitude,
                    'lng' => $location->longitude,
                    'title' => $location->location,
                    'markerType' => 'blue',
                    'totalTrees' => $totalTrees,
                    'popup' => "<div class='p-2'>
                        <h4 class='font-bold text-lg mb-2' style='color: #1e40af; display: block;'>{$location->location}</h4>
                        <p class='mb-1'><span class='font-medium'>LGA:</span> {$divisionName}</p>
                        <p class='mb-1'><span class='font-medium'>Total Trees:</span> {$totalTrees}</p>
                    </div>",
                ];
            })
            ->values()
            ->toArray();
    }
}
