<?php

use App\Models\Division;
use App\Models\Picture;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $this->division      = Division::forceCreate(['LGA_name' => 'Test Division']);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');

    $this->owner = User::factory()->create();
    $this->owner->assignRole('Grower');
});

function squareBoundaryAround(float $lat, float $lng, float $delta = 0.01): array
{
    return [
        'type'        => 'Polygon',
        'coordinates' => [[
            [$lng - $delta, $lat - $delta],
            [$lng + $delta, $lat - $delta],
            [$lng + $delta, $lat + $delta],
            [$lng - $delta, $lat + $delta],
            [$lng - $delta, $lat - $delta],
        ]],
    ];
}

function makeTestPicture(PlantingLocation $location, User $owner, array $overrides = []): Picture
{
    return Picture::create(array_merge([
        'user_id'              => $owner->id,
        'planting_location_id' => $location->id,
        'path'                 => 'pictures/test.jpg',
        'thumbnail'            => 'pictures/test-thumb.jpg',
    ], $overrides));
}

it('includes photos with captured coordinates and excludes photos without them', function () {
    $location = PlantingLocation::create([
        'location'    => 'Photo Map Location',
        'division_id' => $this->division->id,
        'status_id'   => $this->statusPlanned->id,
        'user_id'     => $this->owner->id,
        'latitude'    => 9.0820,
        'longitude'   => 8.6753,
    ]);

    makeTestPicture($location, $this->owner, [
        'captured_latitude'  => 9.0820,
        'captured_longitude' => 8.6753,
        'capture_source'     => 'exif',
        'captured_at'        => now(),
    ]);

    // No captured coordinates at all — must be excluded from the map data.
    makeTestPicture($location, $this->owner);

    $response = $this->actingAs($this->admin)
        ->get(route('planting-locations.show', $location));

    $response->assertOk();
    $response->assertViewHas('photos', function ($photos) {
        return $photos->count() === 1
            && (float) $photos->first()['lat'] === 9.0820
            && (float) $photos->first()['lng'] === 8.6753
            && $photos->first()['capture_source_label'] === 'From photo EXIF';
    });
});

it('flags a photo whose captured coordinates fall inside the boundary as inside', function () {
    $lat = 9.0;
    $lng = 8.0;

    $location = PlantingLocation::create([
        'location'         => 'Inside Boundary Location',
        'division_id'      => $this->division->id,
        'status_id'        => $this->statusPlanned->id,
        'user_id'          => $this->owner->id,
        'latitude'         => $lat,
        'longitude'        => $lng,
        'boundary_geojson' => squareBoundaryAround($lat, $lng),
    ]);

    makeTestPicture($location, $this->owner, [
        'captured_latitude'  => $lat,
        'captured_longitude' => $lng,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('planting-locations.show', $location));

    $response->assertOk();
    $response->assertViewHas('photos', function ($photos) {
        return $photos->first()['inside_boundary'] === true;
    });
});

it('flags a photo whose captured coordinates fall outside the boundary as outside', function () {
    $lat = 9.0;
    $lng = 8.0;

    $location = PlantingLocation::create([
        'location'         => 'Outside Boundary Location',
        'division_id'      => $this->division->id,
        'status_id'        => $this->statusPlanned->id,
        'user_id'          => $this->owner->id,
        'latitude'         => $lat,
        'longitude'        => $lng,
        'boundary_geojson' => squareBoundaryAround($lat, $lng, 0.001),
    ]);

    makeTestPicture($location, $this->owner, [
        'captured_latitude'  => $lat + 5,
        'captured_longitude' => $lng + 5,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('planting-locations.show', $location));

    $response->assertOk();
    $response->assertViewHas('photos', function ($photos) {
        return $photos->first()['inside_boundary'] === false;
    });
});

it('gives a photo a null flag, not false, when the location has no boundary at all', function () {
    $lat = 9.0;
    $lng = 8.0;

    $location = PlantingLocation::create([
        'location'    => 'No Boundary Photo Location',
        'division_id' => $this->division->id,
        'status_id'   => $this->statusPlanned->id,
        'user_id'     => $this->owner->id,
        'latitude'    => $lat,
        'longitude'   => $lng,
    ]);

    makeTestPicture($location, $this->owner, [
        'captured_latitude'  => $lat,
        'captured_longitude' => $lng,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('planting-locations.show', $location));

    $response->assertOk();
    $response->assertViewHas('photos', function ($photos) {
        return $photos->first()['inside_boundary'] === null;
    });
});

// INTENTIONAL: as of the "Photo capture-location markers now public on
// both pages" decision in DECISIONS.md, captured photo coordinates and the
// inside/outside boundary flag ARE now exposed on the public page — this
// deliberately reverses Phase 4's original admin-only stance for this
// specific data (EXIF extraction/storage itself is unchanged). An earlier
// version of this test suite asserted the opposite (a privacy guard
// keeping this data admin-only); that assertion was correct for that
// version of the decision and has been replaced here, not accidentally
// dropped. A future reader diffing test history should read this comment
// before assuming a privacy regression slipped through uncaught.
it('includes the same captured-coordinate photo data on the public page as the admin page', function () {
    $lat = 9.0820;
    $lng = 8.6753;

    $location = PlantingLocation::create([
        'location'         => 'Public Photo Markers Location',
        'division_id'      => $this->division->id,
        'status_id'        => $this->statusPlanned->id,
        'user_id'          => $this->owner->id,
        'latitude'         => $lat,
        'longitude'        => $lng,
        'boundary_geojson' => squareBoundaryAround($lat, $lng),
    ]);

    makeTestPicture($location, $this->owner, [
        'captured_latitude'  => $lat,
        'captured_longitude' => $lng,
        'capture_source'     => 'device_geolocation',
        'captured_at'        => now(),
    ]);

    $response = $this->get(route('public.planting-locations.show', $location->public_code));

    $response->assertOk();
    $response->assertViewHas('photos', function ($photos) {
        return $photos->count() === 1
            && (float) $photos->first()['lat'] === 9.0820
            && (float) $photos->first()['lng'] === 8.6753
            && $photos->first()['inside_boundary'] === true
            && $photos->first()['capture_source_label'] === 'Device GPS at capture';
    });
});

it('flags a photo on the public page as outside the boundary the same way the admin page does', function () {
    $lat = 9.0;
    $lng = 8.0;

    $location = PlantingLocation::create([
        'location'         => 'Public Outside Boundary Location',
        'division_id'      => $this->division->id,
        'status_id'        => $this->statusPlanned->id,
        'user_id'          => $this->owner->id,
        'latitude'         => $lat,
        'longitude'        => $lng,
        'boundary_geojson' => squareBoundaryAround($lat, $lng, 0.001),
    ]);

    makeTestPicture($location, $this->owner, [
        'captured_latitude'  => $lat + 5,
        'captured_longitude' => $lng + 5,
    ]);

    $response = $this->get(route('public.planting-locations.show', $location->public_code));

    $response->assertOk();
    $response->assertViewHas('photos', function ($photos) {
        return $photos->first()['inside_boundary'] === false;
    });
});
