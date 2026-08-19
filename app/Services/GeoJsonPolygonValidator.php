<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

/**
 * Structural validation for a single-ring GeoJSON Polygon submitted as
 * boundary_geojson — not full GeoJSON-spec compliance tooling, just
 * enough to reject garbage and auto-close a plausible unclosed ring.
 */
class GeoJsonPolygonValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(?string $rawJson): ?array
    {
        if ($rawJson === null || trim($rawJson) === '') {
            return null;
        }

        $data = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            $this->fail('Boundary must be valid JSON.');
        }

        if (($data['type'] ?? null) !== 'Polygon') {
            $this->fail('Boundary must be a GeoJSON Polygon.');
        }

        $coordinates = $data['coordinates'] ?? null;

        if (! is_array($coordinates) || count($coordinates) < 1) {
            $this->fail('Boundary must contain at least one ring.');
        }

        $ring = $coordinates[0];

        // Raw minimum before closing: at least 3 positions, otherwise
        // there's no way this can become a real polygon even after
        // auto-closing.
        if (! is_array($ring) || count($ring) < 3) {
            $this->fail('Boundary must have at least 3 distinct vertices.');
        }

        foreach ($ring as $position) {
            $this->validatePosition($position);
        }

        $ring = $this->closeRing($ring);

        // After closing: fewer than 4 positions means fewer than 3
        // distinct vertices (e.g. two points submitted as a degenerate
        // "closed" ring) — still not a real polygon.
        if (count($ring) < 4) {
            $this->fail('Boundary must have at least 3 distinct vertices.');
        }

        $data['coordinates'][0] = $ring;

        return $data;
    }

    private function validatePosition(mixed $position): void
    {
        if (! is_array($position) || count($position) !== 2) {
            $this->fail('Each boundary position must be a [lng, lat] pair.');
        }

        [$lng, $lat] = $position;

        if (! is_numeric($lng) || ! is_numeric($lat)) {
            $this->fail('Each boundary position must contain numeric [lng, lat] values.');
        }

        if ((float) $lng < -180 || (float) $lng > 180) {
            $this->fail('Boundary longitude must be between -180 and 180.');
        }

        if ((float) $lat < -90 || (float) $lat > 90) {
            $this->fail('Boundary latitude must be between -90 and 90.');
        }
    }

    /**
     * Auto-closes an unclosed ring by appending a copy of the first
     * position, rather than rejecting the submission — Leaflet.draw's
     * raw output and manual GeoJSON construction can both plausibly omit
     * the closing point, and it's trivially fixable.
     */
    private function closeRing(array $ring): array
    {
        $first = $ring[0];
        $last  = $ring[count($ring) - 1];

        if (! $this->positionsEqual($first, $last)) {
            $ring[] = $first;
        }

        return $ring;
    }

    private function positionsEqual(mixed $a, mixed $b): bool
    {
        if (! is_array($a) || ! is_array($b) || count($a) !== count($b)) {
            return false;
        }

        foreach ($a as $i => $value) {
            if ((float) $value !== (float) ($b[$i] ?? null)) {
                return false;
            }
        }

        return true;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['boundary_geojson' => $message]);
    }
}
