<?php

use App\Models\ChangeLog;
use App\Models\Division;
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
});

function boundaryPayload(array $coordinates): string
{
    return json_encode(['type' => 'Polygon', 'coordinates' => [$coordinates]]);
}

function closedSquare(): array
{
    return [[7.0, 9.0], [7.001, 9.0], [7.001, 9.001], [7.0, 9.001], [7.0, 9.0]];
}

function differentSquare(): array
{
    return [[7.0, 9.0], [7.002, 9.0], [7.002, 9.002], [7.0, 9.002], [7.0, 9.0]];
}

it('stores a valid boundary and logs boundary_set with the correct fingerprint', function () {
    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'         => 'Boundary Set Location',
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload(closedSquare()),
        ])
        ->assertRedirect();

    $location = PlantingLocation::where('location', 'Boundary Set Location')->firstOrFail();

    expect($location->boundary_geojson)->not->toBeNull();
    expect($location->boundary_geojson['type'])->toBe('Polygon');

    $log = ChangeLog::forEntity('PlantingLocation', $location->id)
        ->where('action', 'boundary_set')
        ->firstOrFail();

    expect($log->old_values)->toBeNull();
    expect($log->new_values['vertex_count'])->toBe(4);
    expect($log->new_values)->toHaveKey('bounding_box');
    expect($log->new_values)->toHaveKey('hash');
});

it('does not log boundary_set when no boundary is provided on create', function () {
    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'    => 'No Boundary Location',
            'division_id' => $this->division->id,
            'status_id'   => $this->statusPlanned->id,
        ])
        ->assertRedirect();

    $location = PlantingLocation::where('location', 'No Boundary Location')->firstOrFail();

    expect($location->boundary_geojson)->toBeNull();
    expect(ChangeLog::forEntity('PlantingLocation', $location->id)->where('action', 'boundary_set')->count())->toBe(0);
});

it('logs boundary_updated with only fingerprint fields (no raw coordinates) when the boundary genuinely changes', function () {
    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'         => 'Update Boundary Location',
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload(closedSquare()),
        ]);

    $location = PlantingLocation::where('location', 'Update Boundary Location')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('planting-locations.update', $location), [
            'location'         => $location->location,
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload(differentSquare()),
        ])
        ->assertRedirect(route('planting-locations.show', $location));

    $log = ChangeLog::forEntity('PlantingLocation', $location->id)
        ->where('action', 'boundary_updated')
        ->firstOrFail();

    expect($log->old_values)->toHaveKeys(['vertex_count', 'bounding_box', 'hash']);
    expect($log->new_values)->toHaveKeys(['vertex_count', 'bounding_box', 'hash']);
    expect($log->old_values['hash'])->not->toBe($log->new_values['hash']);

    // No raw coordinates array anywhere in either side of the log.
    expect($log->old_values)->not->toHaveKey('coordinates');
    expect($log->new_values)->not->toHaveKey('coordinates');
    expect($log->old_values)->not->toHaveKey('type');
    expect($log->new_values)->not->toHaveKey('type');
});

it('does not log boundary_updated when the same shape is resubmitted with different formatting', function () {
    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'         => 'Noop Boundary Location',
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload(closedSquare()),
        ]);

    $location = PlantingLocation::where('location', 'Noop Boundary Location')->firstOrFail();

    $reformatted = [
        [7.0000000, 9.0000000],
        [7.0010000, 9.0],
        [7.001, 9.0010000],
        [7.0, 9.001],
        [7.00000, 9.00000],
    ];

    $this->actingAs($this->admin)
        ->put(route('planting-locations.update', $location), [
            'location'         => $location->location,
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload($reformatted),
        ])
        ->assertRedirect(route('planting-locations.show', $location));

    expect(ChangeLog::forEntity('PlantingLocation', $location->id)->where('action', 'boundary_updated')->count())->toBe(0);
});

it('rejects a boundary with fewer than 3 distinct vertices', function () {
    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'         => 'Too Few Vertices Location',
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload([[7.0, 9.0], [7.001, 9.0]]),
        ])
        ->assertSessionHasErrors(['boundary_geojson']);

    expect(PlantingLocation::where('location', 'Too Few Vertices Location')->exists())->toBeFalse();
});

it('accepts an unclosed ring and saves it correctly auto-closed', function () {
    $unclosed = [[7.0, 9.0], [7.001, 9.0], [7.0005, 9.001]]; // 3 distinct vertices, not closed

    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'         => 'Unclosed Ring Location',
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload($unclosed),
        ])
        ->assertRedirect();

    $location = PlantingLocation::where('location', 'Unclosed Ring Location')->firstOrFail();
    $ring = $location->boundary_geojson['coordinates'][0];

    expect($ring)->toHaveCount(4);
    expect($ring[0])->toBe($ring[count($ring) - 1]);
});

it('rejects a ring shaped like [A, B, A] that collapses to only 2 distinct vertices once closed', function () {
    // 3 raw positions passes the pre-closing raw-length check (3 >= 3).
    // closeRing() sees first == last already (A == A), so it does NOT
    // append anything — the ring stays [A, B, A], which is only 2
    // distinct vertices, not 3. Must be caught by the post-closing
    // count(<4) check, not the pre-closing one.
    $degenerate = [[7.0, 9.0], [7.001, 9.0], [7.0, 9.0]];

    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'         => 'Degenerate ABA Boundary Location',
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload($degenerate),
        ])
        ->assertSessionHasErrors(['boundary_geojson']);

    expect(PlantingLocation::where('location', 'Degenerate ABA Boundary Location')->exists())->toBeFalse();
});

it('rejects an out-of-range coordinate within the polygon', function () {
    $invalid = [[7.0, 9.0], [7.001, 9.0], [7.001, 200], [7.0, 9.001], [7.0, 9.0]];

    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'         => 'Out Of Range Boundary Location',
            'division_id'      => $this->division->id,
            'status_id'        => $this->statusPlanned->id,
            'boundary_geojson' => boundaryPayload($invalid),
        ])
        ->assertSessionHasErrors(['boundary_geojson']);

    expect(PlantingLocation::where('location', 'Out Of Range Boundary Location')->exists())->toBeFalse();
});
