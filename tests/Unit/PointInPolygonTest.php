<?php

use App\Services\PointInPolygon;

function lShapedBoundary(): array
{
    // An L-shape: a 4x4 square with the upper-right 2x2 quadrant (lng
    // 2-4, lat 2-4) cut out. Deliberately concave, so a naive
    // bounding-box check would get points in the notch wrong.
    return [
        'type'        => 'Polygon',
        'coordinates' => [[
            [0, 0], [4, 0], [4, 2], [2, 2], [2, 4], [0, 4], [0, 0],
        ]],
    ];
}

it('returns true for a point clearly inside a simple rectangular polygon', function () {
    $polygon = [
        'type'        => 'Polygon',
        'coordinates' => [[
            [0, 0], [10, 0], [10, 10], [0, 10], [0, 0],
        ]],
    ];

    $service = new PointInPolygon();

    expect($service->isInside(lat: 5, lng: 5, boundaryGeojson: $polygon))->toBeTrue();
});

it('returns false for a point clearly outside a simple rectangular polygon', function () {
    $polygon = [
        'type'        => 'Polygon',
        'coordinates' => [[
            [0, 0], [10, 0], [10, 10], [0, 10], [0, 0],
        ]],
    ];

    $service = new PointInPolygon();

    expect($service->isInside(lat: 50, lng: 50, boundaryGeojson: $polygon))->toBeFalse();
});

it('resolves a point inside the notch of a concave L-shaped polygon as outside', function () {
    $service = new PointInPolygon();

    // (lng 3, lat 3) sits within the L-shape's overall bounding box, but
    // inside the notch that was cut out of it — this is the case that
    // actually exercises ray-casting rather than a simpler bounding-box
    // approximation.
    expect($service->isInside(lat: 3, lng: 3, boundaryGeojson: lShapedBoundary()))->toBeFalse();
});

it('resolves points inside each leg of a concave L-shaped polygon as inside', function () {
    $service = new PointInPolygon();

    expect($service->isInside(lat: 1, lng: 1, boundaryGeojson: lShapedBoundary()))->toBeTrue();
    expect($service->isInside(lat: 1, lng: 3, boundaryGeojson: lShapedBoundary()))->toBeTrue();
    expect($service->isInside(lat: 3, lng: 1, boundaryGeojson: lShapedBoundary()))->toBeTrue();
});
