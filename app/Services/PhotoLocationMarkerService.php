<?php

namespace App\Services;

use App\Models\PlantingLocation;
use Illuminate\Support\Collection;

/**
 * Builds the annotated photo-location marker list consumed by
 * map2.blade.php's :photos prop. Shared by PlantingLocationController
 * and PublicPlantingLocationController — both pages now render this data
 * (see DECISIONS.md: "Photo capture-location markers now public on both
 * pages"), so this exists specifically to avoid duplicating the same
 * mapping logic in two controllers.
 */
class PhotoLocationMarkerService
{
    public function __construct(private PointInPolygon $pointInPolygon)
    {
    }

    public function build(PlantingLocation $plantingLocation): Collection
    {
        return $plantingLocation->pictures
            ->filter(fn ($picture) => $picture->captured_latitude !== null && $picture->captured_longitude !== null)
            ->map(function ($picture) use ($plantingLocation) {
                // Only ever computed when a boundary actually exists to check
                // against — null (not false) means "nothing to compare
                // against", never a false "outside boundary" flag with no basis.
                $insideBoundary = $plantingLocation->boundary_geojson !== null
                    ? $this->pointInPolygon->isInside(
                        (float) $picture->captured_latitude,
                        (float) $picture->captured_longitude,
                        $plantingLocation->boundary_geojson,
                    )
                    : null;

                return [
                    'lat'                  => (float) $picture->captured_latitude,
                    'lng'                  => (float) $picture->captured_longitude,
                    'thumb_url'            => asset('storage/' . ($picture->thumbnail ?: $picture->path)),
                    'full_url'             => asset('storage/' . $picture->path),
                    'captured_at'          => $picture->captured_at?->format('Y-m-d H:i'),
                    'capture_source_label' => match ($picture->capture_source) {
                        'exif'                => 'From photo EXIF',
                        'device_geolocation'  => 'Device GPS at capture',
                        default               => null,
                    },
                    'inside_boundary'      => $insideBoundary,
                ];
            })
            ->values();
    }
}
