<?php

use App\Models\Division;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\User;
use App\Services\NearbyLocationFinder;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $this->division      = Division::forceCreate(['LGA_name' => 'Test Division']);

    $this->owner = User::factory()->create();
    $this->owner->assignRole('Grower');

    $this->finder = new NearbyLocationFinder();
});

function nearbyTestLocation(array $overrides = []): PlantingLocation
{
    return PlantingLocation::create(array_merge([
        'location'    => 'Location ' . uniqid(),
        'division_id' => \App\Models\Division::first()->id,
        'status_id'   => \App\Models\PlantingLocationStatus::first()->id,
        'user_id'     => \App\Models\User::first()->id,
    ], $overrides));
}

it('returns a location that falls within the bounding box', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    // Roughly 5.5km north — well within the default 10km radius.
    $nearby = nearbyTestLocation(['latitude' => 9.050, 'longitude' => 8.000]);

    $results = $this->finder->find($origin);

    expect($results->pluck('id'))->toContain($nearby->id);
});

it('excludes a location that is clearly outside the bounding box', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    // Roughly 220km away — clearly outside the default 10km radius.
    $far = nearbyTestLocation(['latitude' => 11.000, 'longitude' => 8.000]);

    $results = $this->finder->find($origin);

    expect($results->pluck('id'))->not->toContain($far->id);
});

it('never includes the current location in its own results', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    $results = $this->finder->find($origin);

    expect($results->pluck('id'))->not->toContain($origin->id);
});

it('excludes locations with null latitude/longitude without erroring', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    $noCoords = nearbyTestLocation(['latitude' => null, 'longitude' => null]);

    $results = $this->finder->find($origin);

    expect($results->pluck('id'))->not->toContain($noCoords->id);
});

it('returns an empty collection, not an error, when the current location itself has no coordinates', function () {
    $origin = nearbyTestLocation(['latitude' => null, 'longitude' => null]);
    nearbyTestLocation(['latitude' => 9.001, 'longitude' => 8.001]);

    $results = $this->finder->find($origin);

    expect($results)->toHaveCount(0);
});

it('caps results at the configured limit even when more locations match', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    // 5 locations all well within range, but ask for at most 3.
    for ($i = 0; $i < 5; $i++) {
        nearbyTestLocation(['latitude' => 9.001 + ($i * 0.001), 'longitude' => 8.001]);
    }

    $results = $this->finder->find($origin, radiusKm: 10.0, limit: 3);

    expect($results)->toHaveCount(3);
});

it('returns an empty collection, not an error, when nothing else is nearby', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    $results = $this->finder->find($origin);

    expect($results)->toHaveCount(0);
});

it('includes only id, location, latitude, longitude, and boundary_geojson on each result', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    nearbyTestLocation([
        'latitude'  => 9.010,
        'longitude' => 8.010,
        'comment'   => 'Should not leak into the result set.',
    ]);

    $results = $this->finder->find($origin);

    expect($results)->toHaveCount(1);
    expect($results->first()->getAttributes())
        ->toHaveKeys(['id', 'location', 'latitude', 'longitude', 'boundary_geojson'])
        ->not->toHaveKey('comment');
});

it('renders the admin show page successfully with a nearby neighbor that has a boundary', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    nearbyTestLocation([
        'location'         => 'Neighbor With Boundary',
        'latitude'         => 9.010,
        'longitude'        => 8.010,
        'boundary_geojson' => [
            'type'        => 'Polygon',
            'coordinates' => [[[8.009, 9.009], [8.011, 9.009], [8.011, 9.011], [8.009, 9.011], [8.009, 9.009]]],
        ],
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->get(route('planting-locations.show', $origin));

    $response->assertOk();
    $response->assertViewHas('neighbors', function ($neighbors) {
        return $neighbors->count() === 1 && $neighbors->first()->location === 'Neighbor With Boundary';
    });
});

it('renders the edit page successfully with a nearby neighbor that has a boundary', function () {
    $origin = nearbyTestLocation(['latitude' => 9.000, 'longitude' => 8.000]);

    nearbyTestLocation([
        'location'         => 'Neighbor With Boundary',
        'latitude'         => 9.010,
        'longitude'        => 8.010,
        'boundary_geojson' => [
            'type'        => 'Polygon',
            'coordinates' => [[[8.009, 9.009], [8.011, 9.009], [8.011, 9.011], [8.009, 9.011], [8.009, 9.009]]],
        ],
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->get(route('planting-locations.edit', $origin));

    $response->assertOk();
    $response->assertViewHas('neighbors', function ($neighbors) {
        return $neighbors->count() === 1 && $neighbors->first()->location === 'Neighbor With Boundary';
    });
});
