<?php

namespace App\Services;

/**
 * Approximates a GeoJSON Polygon's area, in hectares, by projecting its
 * outer ring into local planar meters and applying the standard shoelace
 * formula. Uses the same latitude-adjusted degree-to-km approximation
 * NearbyLocationFinder already relies on for its bounding box — good
 * enough for a rough area/density figure, not survey-grade precision.
 * Consistent with Phase 5's own decision to avoid full GIS tooling for
 * this app.
 */
class PolygonAreaCalculator
{
    /**
     * Standard approximation for the distance covered by one degree of
     * latitude, matching NearbyLocationFinder's own constant.
     */
    private const METERS_PER_DEGREE_LATITUDE = 111_000.0;

    /**
     * @param  array|null  $boundaryGeojson  A GeoJSON Polygon, e.g.
     *                                       ['type' => 'Polygon', 'coordinates' => [[[lng, lat], ...]]]
     * @return float|null  Area in hectares, or null when there's no
     *                      boundary to calculate from — not a real zero.
     */
    public function calculateHectares(?array $boundaryGeojson): ?float
    {
        $ring = $boundaryGeojson['coordinates'][0] ?? null;

        if (! $ring || count($ring) < 3) {
            return null;
        }

        $points = $this->projectToMeters($ring);

        return $this->shoelaceAreaSquareMeters($points) / 10_000;
    }

    /**
     * Projects each [lng, lat] position onto a local planar x/y (meters),
     * using the ring's first vertex as the origin. Longitude degrees
     * shrink toward the poles, so the longitude axis is scaled by
     * cos(latitude) — same approach, and the same near-pole guard, as
     * NearbyLocationFinder's bounding box.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    private function projectToMeters(array $ring): array
    {
        $originLng = (float) $ring[0][0];
        $originLat = (float) $ring[0][1];
        $cosLat = max(abs(cos(deg2rad($originLat))), 0.01);

        return array_map(function ($position) use ($originLng, $originLat, $cosLat) {
            $lng = (float) $position[0];
            $lat = (float) $position[1];

            $x = ($lng - $originLng) * self::METERS_PER_DEGREE_LATITUDE * $cosLat;
            $y = ($lat - $originLat) * self::METERS_PER_DEGREE_LATITUDE;

            return [$x, $y];
        }, $ring);
    }

    /**
     * Standard shoelace formula: 0.5 * |Σ(x_i * y_{i+1} - x_{i+1} * y_i)|.
     * Works whether or not the ring's last position duplicates its first
     * (a closed ring) — a duplicated closing point just contributes a
     * zero-area term, so no special-casing is needed here.
     */
    private function shoelaceAreaSquareMeters(array $points): float
    {
        $sum = 0.0;
        $count = count($points);

        for ($i = 0; $i < $count; $i++) {
            [$x1, $y1] = $points[$i];
            [$x2, $y2] = $points[($i + 1) % $count];

            $sum += ($x1 * $y2) - ($x2 * $y1);
        }

        return abs($sum) / 2;
    }
}
