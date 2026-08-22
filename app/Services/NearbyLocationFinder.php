<?php

namespace App\Services;

use App\Models\PlantingLocation;
use Illuminate\Support\Collection;

/**
 * Finds other PlantingLocations "adjacent" to a given one, for display as
 * dimmed context markers on the show/edit maps — not a general-purpose
 * proximity/distance feature. "Adjacent" is a plain latitude/longitude
 * bounding box, not true circular-radius/Haversine distance and not any
 * spatial database feature — consistent with Phase 5's own decision to
 * keep boundary_geojson a plain JSON column rather than adopt spatial
 * storage, since this app has no confirmed need for real distance math.
 */
class NearbyLocationFinder
{
    /**
     * Default search radius, in kilometers. A named constant rather than a
     * magic number buried in the query, so it's easy to find and change.
     */
    public const DEFAULT_RADIUS_KM = 10.0;

    /**
     * Defensive cap on result count, in case a dense cluster of locations
     * would otherwise produce an unbounded response.
     */
    public const DEFAULT_LIMIT = 50;

    /**
     * Standard approximation for the distance covered by one degree of
     * latitude. Good enough for a rough bounding-box prefilter — this is
     * deliberately not a precise geodesic calculation.
     */
    private const KM_PER_DEGREE_LATITUDE = 111.0;

    /**
     * @return Collection<int, PlantingLocation> Each result carries only
     *         id, location, latitude, longitude, and boundary_geojson —
     *         no other fields are selected.
     */
    public function find(
        PlantingLocation $location,
        float $radiusKm = self::DEFAULT_RADIUS_KM,
        int $limit = self::DEFAULT_LIMIT
    ): Collection {
        if ($location->latitude === null || $location->longitude === null) {
            return collect();
        }

        $box = $this->boundingBox((float) $location->latitude, (float) $location->longitude, $radiusKm);

        return PlantingLocation::query()
            ->select(['id', 'location', 'latitude', 'longitude', 'boundary_geojson'])
            ->where('id', '!=', $location->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$box['min_lat'], $box['max_lat']])
            ->whereBetween('longitude', [$box['min_lng'], $box['max_lng']])
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{min_lat: float, max_lat: float, min_lng: float, max_lng: float}
     */
    private function boundingBox(float $lat, float $lng, float $radiusKm): array
    {
        $latDelta = $radiusKm / self::KM_PER_DEGREE_LATITUDE;

        // Longitude degrees shrink toward the poles (a degree of longitude
        // covers less ground distance the further you are from the
        // equator), so the longitude delta needs dividing by cos(latitude).
        // Guarded against a near-zero cosine — practically unreachable for
        // this app's real-world data (nowhere near the poles), but handled
        // gracefully rather than assumed impossible, since an unguarded
        // division here could otherwise blow up toward an unbounded delta.
        $cosLat = max(abs(cos(deg2rad($lat))), 0.01);
        $lngDelta = $radiusKm / (self::KM_PER_DEGREE_LATITUDE * $cosLat);

        return [
            'min_lat' => $lat - $latDelta,
            'max_lat' => $lat + $latDelta,
            'min_lng' => $lng - $lngDelta,
            'max_lng' => $lng + $lngDelta,
        ];
    }
}
