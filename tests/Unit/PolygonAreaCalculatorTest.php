<?php

use App\Services\PolygonAreaCalculator;

it('computes area in the right ballpark for a small rectangle near the equator', function () {
    // 0.001° square at the equator, where 1° ≈ 111,000m in both
    // directions (cos(0°) = 1, no longitude shrinkage). Each side is
    // therefore ≈111m, giving a hand-calculable expected area of
    // 111m × 111m = 12,321 sqm = 1.2321 ha.
    $polygon = [
        'type'        => 'Polygon',
        'coordinates' => [[
            [0, 0], [0.001, 0], [0.001, 0.001], [0, 0.001], [0, 0],
        ]],
    ];

    $service = new PolygonAreaCalculator();

    expect(round($service->calculateHectares($polygon), 2))->toBe(1.23);
});

it('returns null, not zero, when there is no boundary to calculate from', function () {
    $service = new PolygonAreaCalculator();

    expect($service->calculateHectares(null))->toBeNull();
});

it('returns a positive area regardless of the ring winding direction', function () {
    $clockwise = [
        'type'        => 'Polygon',
        'coordinates' => [[
            [0, 0], [0, 0.001], [0.001, 0.001], [0.001, 0], [0, 0],
        ]],
    ];

    $service = new PolygonAreaCalculator();

    expect($service->calculateHectares($clockwise))->toBeGreaterThan(0);
});
