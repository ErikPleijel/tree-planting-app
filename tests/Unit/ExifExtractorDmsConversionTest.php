<?php

use App\Services\ExifExtractor;

function invokeDmsToDecimal(array $dms, string $ref): ?float
{
    $extractor = new ExifExtractor();
    $method = new ReflectionMethod(ExifExtractor::class, 'dmsToDecimal');
    $method->setAccessible(true);

    return $method->invoke($extractor, $dms, $ref);
}

it('converts a Southern-hemisphere DMS coordinate to a negative decimal degree', function () {
    // 33 deg 51' 35" S -> -33.8597...
    $decimal = invokeDmsToDecimal(['33/1', '51/1', '35/1'], 'S');

    expect($decimal)->toBeLessThan(0);
    expect(round($decimal, 4))->toBe(-33.8597);
});

it('converts a Western-hemisphere DMS coordinate to a negative decimal degree', function () {
    // 122 deg 25' 9" W -> -122.4192...
    $decimal = invokeDmsToDecimal(['122/1', '25/1', '9/1'], 'W');

    expect($decimal)->toBeLessThan(0);
    expect(round($decimal, 4))->toBe(-122.4192);
});

it('converts a Northern/Eastern DMS coordinate to a positive decimal degree', function () {
    // 40 deg 26' 46" N -> 40.4461...
    $decimal = invokeDmsToDecimal(['40/1', '26/1', '46/1'], 'N');

    expect($decimal)->toBeGreaterThan(0);
    expect(round($decimal, 4))->toBe(40.4461);
});

it('returns null when the DMS array has fewer than three components', function () {
    expect(invokeDmsToDecimal(['33/1', '51/1'], 'S'))->toBeNull();
});
