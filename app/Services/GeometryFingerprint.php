<?php

namespace App\Services;

/**
 * Produces a lightweight, loggable summary of a GeoJSON Polygon — never
 * the raw coordinate array itself. Used by ChangeLogger writes for
 * boundary_set/boundary_updated, since storing full geometry on every
 * edit in change_logs' unindexed JSON columns would be a real bloat
 * vector for anything beyond a handful of vertices.
 */
class GeometryFingerprint
{
    public function fingerprint(array $geojson): array
    {
        $ring = $geojson['coordinates'][0] ?? [];

        // The outer ring's last position duplicates the first (a closed
        // ring) — don't count that repeat as a distinct vertex.
        $vertices = $ring;
        if (count($vertices) > 1 && $vertices[0] == $vertices[count($vertices) - 1]) {
            array_pop($vertices);
        }

        $lngs = array_map(fn ($position) => (float) $position[0], $vertices);
        $lats = array_map(fn ($position) => (float) $position[1], $vertices);

        return [
            'vertex_count' => count($vertices),
            'bounding_box' => [
                'min_lat' => $lats === [] ? null : min($lats),
                'max_lat' => $lats === [] ? null : max($lats),
                'min_lng' => $lngs === [] ? null : min($lngs),
                'max_lng' => $lngs === [] ? null : max($lngs),
            ],
            'hash' => $this->hash($ring),
        ];
    }

    /**
     * Deterministic hash of the ring's coordinates. Vertex order is
     * preserved (order defines the polygon's shape and is never
     * reordered), but each coordinate's float formatting is normalized
     * first so incidental precision/trailing-zero differences from how
     * the geometry was serialized don't change the hash for an otherwise
     * identical shape.
     */
    private function hash(array $ring): string
    {
        $normalized = array_map(
            fn ($position) => array_map([$this, 'normalizeCoordinate'], $position),
            $ring
        );

        return sha1(json_encode($normalized));
    }

    private function normalizeCoordinate($coordinate): string
    {
        // ~1cm precision at the equator — enough to absorb float noise
        // without discarding real precision a hand-drawn or GPS-derived
        // boundary would actually have.
        $formatted = number_format((float) $coordinate, 7, '.', '');
        $trimmed = rtrim(rtrim($formatted, '0'), '.');

        return $trimmed === '' || $trimmed === '-' ? '0' : $trimmed;
    }
}
