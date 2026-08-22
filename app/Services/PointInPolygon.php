<?php

namespace App\Services;

/**
 * Ray-casting point-in-polygon test against a single-ring GeoJSON Polygon's
 * outer ring. This app's boundary_geojson never contains multiple rings or
 * holes (Phase 5's own storage constraint), so this deliberately only reads
 * coordinates[0] and doesn't attempt to handle anything more elaborate.
 */
class PointInPolygon
{
    /**
     * @param  array  $boundaryGeojson  A GeoJSON Polygon, e.g.
     *                                  ['type' => 'Polygon', 'coordinates' => [[[lng, lat], ...]]]
     */
    public function isInside(float $lat, float $lng, array $boundaryGeojson): bool
    {
        $ring = $boundaryGeojson['coordinates'][0] ?? [];

        $inside = false;
        $count = count($ring);

        // Standard ray-casting (PNPOLY) algorithm: cast a ray from the
        // point in the +lng direction and count how many ring edges it
        // crosses. An odd number of crossings means the point is inside.
        // A point that falls exactly on an edge is an inherently ambiguous
        // case for this algorithm — it may resolve either way depending on
        // floating-point rounding — and isn't specially handled here, since
        // it's a rare edge case not worth extra complexity for this
        // feature (a "possibly on the line" photo, not a clear violation).
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            [$lngI, $latI] = $ring[$i];
            [$lngJ, $latJ] = $ring[$j];

            $edgeCrossesRay = ($latI > $lat) !== ($latJ > $lat)
                && $lng < ($lngJ - $lngI) * ($lat - $latI) / ($latJ - $latI) + $lngI;

            if ($edgeCrossesRay) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
