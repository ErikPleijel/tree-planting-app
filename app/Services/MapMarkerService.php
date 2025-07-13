<?php

namespace App\Services;

use App\Models\PlantingLocation;

class MapMarkerService
{
    public function getMarkers(array $filters = []): array
    {
        $query = PlantingLocation::query();

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


        // ✅ New filter: Division ID
        if (!empty($filters['division_id'])) {
            $query->where('division_id', $filters['division_id']);
        }

        return $query->get()->map(function ($location) {
            return [
                'lat' => $location->latitude,
                'lng' => $location->longitude,
                'popup' => "<strong>{$location->location}</strong><br>{$location->comment}",
            ];
        })->toArray();
    }

}
