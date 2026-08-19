<?php

use App\Services\GeometryFingerprint;

function boundarySquare(): array
{
    return [
        'type' => 'Polygon',
        'coordinates' => [[
            [7.0, 9.0],
            [7.001, 9.0],
            [7.001, 9.001],
            [7.0, 9.001],
            [7.0, 9.0],
        ]],
    ];
}

it('produces the same hash for two differently-formatted representations of the identical shape', function () {
    $fingerprinter = new GeometryFingerprint();

    $canonical = boundarySquare();

    $differentlyFormatted = [
        'type' => 'Polygon',
        'coordinates' => [[
            [7.0000000, 9.0000000],
            [7.0010000, 9.0],
            [7.001, 9.0010000],
            [7.0, 9.001],
            [7.00000, 9.00000],
        ]],
    ];

    $a = $fingerprinter->fingerprint($canonical);
    $b = $fingerprinter->fingerprint($differentlyFormatted);

    expect($a['hash'])->toBe($b['hash']);
    expect($a['vertex_count'])->toBe($b['vertex_count']);
});

it('produces a different hash when even one vertex differs', function () {
    $fingerprinter = new GeometryFingerprint();

    $original = boundarySquare();

    $moved = boundarySquare();
    $moved['coordinates'][0][1] = [7.002, 9.0]; // second vertex moved

    $a = $fingerprinter->fingerprint($original);
    $b = $fingerprinter->fingerprint($moved);

    expect($a['hash'])->not->toBe($b['hash']);
});

it('counts vertices excluding the duplicate closing point', function () {
    $fingerprinter = new GeometryFingerprint();

    $result = $fingerprinter->fingerprint(boundarySquare());

    expect($result['vertex_count'])->toBe(4);
});

it('computes a bounding box matching the outer ring extents', function () {
    $fingerprinter = new GeometryFingerprint();

    $result = $fingerprinter->fingerprint(boundarySquare());

    expect($result['bounding_box'])->toBe([
        'min_lat' => 9.0,
        'max_lat' => 9.001,
        'min_lng' => 7.0,
        'max_lng' => 7.001,
    ]);
});
