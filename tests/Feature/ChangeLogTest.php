<?php

use App\Models\ChangeLog;
use App\Models\Division;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\TreePlanting;
use App\Models\TreePlantingStatus;
use App\Models\TreeType;
use App\Models\User;
use App\Services\ChangeLogger;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    // PlantingLocationStatus has no $fillable (pre-existing, out of scope
    // to change here), so forceCreate() is used instead of create().
    $this->statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $this->statusActive  = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Active']);

    $this->treeStatusUnverified = TreePlantingStatus::create(['tree_planting_status' => 'Unverified']);
    $this->treeStatusVerified   = TreePlantingStatus::create(['tree_planting_status' => 'Verified']);

    // Division likewise has no $fillable (pre-existing, out of scope).
    $this->division = Division::forceCreate(['LGA_name' => 'Test Division']);
    $this->treeType = TreeType::create(['name' => 'Test Tree', 'latin_name' => 'Testus treeus']);

    // Owns the fixture rows; kept distinct from the acting user in tests
    // that delete the acting user, so cascade deletes don't interfere.
    $this->owner = User::factory()->create();
    $this->owner->assignRole('Grower');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

function makeLocation(array $attrs = []): PlantingLocation
{
    return PlantingLocation::create(array_merge([
        'location'    => 'Test Location '.uniqid(),
        'division_id' => test()->division->id,
        'status_id'   => test()->statusPlanned->id,
        'user_id'     => test()->owner->id,
        'latitude'    => 9.000000,
        'longitude'   => 7.000000,
    ], $attrs));
}

function makePlanting(PlantingLocation $location, array $attrs = []): TreePlanting
{
    return TreePlanting::create(array_merge([
        'planting_date'     => now()->subDays(10)->toDateString(),
        'number_of_trees'   => 5,
        'tree_type_id'      => test()->treeType->id,
        'planting_location_id' => $location->id,
        'user_id'           => test()->owner->id,
        'status'            => test()->treeStatusUnverified->id,
        'status_updated_by' => test()->owner->id,
    ], $attrs));
}

it('creates one change log row per moved planting with correct old/new location ids', function () {
    $source      = makeLocation(['location' => 'Source']);
    $destination = makeLocation(['location' => 'Destination']);
    $planting    = makePlanting($source);

    $this->actingAs($this->admin)
        ->post(route('planting-locations.move', $source), [
            'planting_ids'   => [$planting->id],
            'destination_id' => $destination->id,
        ])
        ->assertRedirect(route('planting-locations.show', $source));

    expect($planting->fresh()->planting_location_id)->toBe($destination->id);

    $logs = ChangeLog::forEntity('TreePlanting', $planting->id)->get();

    expect($logs)->toHaveCount(1);
    expect($logs->first()->action)->toBe('moved');
    expect($logs->first()->old_values)->toBe(['planting_location_id' => $source->id]);
    expect($logs->first()->new_values)->toBe(['planting_location_id' => $destination->id]);
});

it('logs multiple moved plantings as separate rows, not one combined row', function () {
    $source      = makeLocation(['location' => 'Source']);
    $destination = makeLocation(['location' => 'Destination']);
    $plantingA   = makePlanting($source);
    $plantingB   = makePlanting($source);

    $this->actingAs($this->admin)
        ->post(route('planting-locations.move', $source), [
            'planting_ids'   => [$plantingA->id, $plantingB->id],
            'destination_id' => $destination->id,
        ])
        ->assertRedirect(route('planting-locations.show', $source));

    expect(ChangeLog::where('action', 'moved')->count())->toBe(2);
    expect(ChangeLog::forEntity('TreePlanting', $plantingA->id)->where('action', 'moved')->count())->toBe(1);
    expect(ChangeLog::forEntity('TreePlanting', $plantingB->id)->where('action', 'moved')->count())->toBe(1);
});

it('logs a coordinates change only when latitude/longitude actually change', function () {
    $location = makeLocation(['latitude' => 9.111111, 'longitude' => 7.222222]);

    // No-op edit: same coordinates, only the comment changes.
    $this->actingAs($this->admin)
        ->put(route('planting-locations.update', $location), [
            'location'    => $location->location,
            'division_id' => $this->division->id,
            'status_id'   => $this->statusPlanned->id,
            'comment'     => 'Updated comment only',
            'latitude'    => 9.111111,
            'longitude'   => 7.222222,
        ])
        ->assertRedirect(route('planting-locations.show', $location));

    expect(ChangeLog::forEntity('PlantingLocation', $location->id)->where('action', 'coordinates_updated')->count())->toBe(0);

    // Real coordinate change.
    $this->actingAs($this->admin)
        ->put(route('planting-locations.update', $location), [
            'location'    => $location->location,
            'division_id' => $this->division->id,
            'status_id'   => $this->statusPlanned->id,
            'comment'     => 'Updated comment only',
            'latitude'    => 9.999999,
            'longitude'   => 7.888888,
        ])
        ->assertRedirect(route('planting-locations.show', $location));

    $logs = ChangeLog::forEntity('PlantingLocation', $location->id)->where('action', 'coordinates_updated')->get();

    expect($logs)->toHaveCount(1);
    expect($logs->first()->new_values['latitude'])->toEqual(9.999999);
    expect($logs->first()->new_values['longitude'])->toEqual(7.888888);
});

it('logs coordinate and status changes as two separate rows when both change in one request', function () {
    $location = makeLocation([
        'latitude'  => 9.111111,
        'longitude' => 7.222222,
        'status_id' => $this->statusPlanned->id,
    ]);

    $this->actingAs($this->admin)
        ->put(route('planting-locations.update', $location), [
            'location'    => $location->location,
            'division_id' => $this->division->id,
            'status_id'   => $this->statusActive->id,
            'comment'     => $location->comment,
            'latitude'    => 9.999999,
            'longitude'   => 7.888888,
        ])
        ->assertRedirect(route('planting-locations.show', $location));

    $logs = ChangeLog::forEntity('PlantingLocation', $location->id)->get();

    expect($logs)->toHaveCount(2);
    expect($logs->pluck('action')->sort()->values()->all())->toBe(['coordinates_updated', 'status_changed']);
});

it('logs a tree planting status change with correct old/new values and attribution', function () {
    $location = makeLocation();
    $planting = makePlanting($location, ['status' => $this->treeStatusUnverified->id]);

    $this->actingAs($this->admin)
        ->put(route('tree-plantings.update', $planting), [
            'planting_date'         => $planting->planting_date->toDateString(),
            'number_of_trees'       => $planting->number_of_trees,
            'tree_type_id'          => $this->treeType->id,
            'planting_location_id'  => $location->id,
            'status'                => $this->treeStatusVerified->id,
        ])
        ->assertRedirect(route('planting-locations.show', $location->id));

    $logs = ChangeLog::forEntity('TreePlanting', $planting->id)->where('action', 'status_changed')->get();

    expect($logs)->toHaveCount(1);
    expect($logs->first()->old_values)->toBe(['status' => $this->treeStatusUnverified->id]);
    expect($logs->first()->new_values)->toBe(['status' => $this->treeStatusVerified->id]);
    expect($logs->first()->changed_by)->toBe($this->admin->id);
    expect($logs->first()->changed_by_name)->toBe($this->admin->name);
});

it('rejects out-of-range latitude and longitude values', function () {
    $location = makeLocation(['latitude' => 9.0, 'longitude' => 7.0]);

    $this->actingAs($this->admin)
        ->put(route('planting-locations.update', $location), [
            'location'    => $location->location,
            'division_id' => $this->division->id,
            'status_id'   => $this->statusPlanned->id,
            'latitude'    => 200,
            'longitude'   => -200,
        ])
        ->assertSessionHasErrors(['latitude', 'longitude']);

    expect($location->fresh()->latitude)->toEqual(9.0);
    expect(ChangeLog::forEntity('PlantingLocation', $location->id)->count())->toBe(0);
});

it('preserves changed_by_name after the acting user is deleted', function () {
    $location = makeLocation(['latitude' => 1.0, 'longitude' => 1.0]);

    $this->actingAs($this->admin)
        ->put(route('planting-locations.update', $location), [
            'location'    => $location->location,
            'division_id' => $this->division->id,
            'status_id'   => $this->statusPlanned->id,
            'latitude'    => 2.0,
            'longitude'   => 2.0,
        ])
        ->assertRedirect(route('planting-locations.show', $location));

    $log = ChangeLog::forEntity('PlantingLocation', $location->id)->where('action', 'coordinates_updated')->firstOrFail();

    expect($log->changed_by)->toBe($this->admin->id);
    $adminName = $this->admin->name;

    $this->admin->delete();

    $log->refresh();

    expect($log->changed_by)->toBeNull();
    expect($log->changed_by_name)->toBe($adminName);
});

it('ignores a caller-supplied created_at and always stamps the real time', function () {
    $farPast = now()->subYears(5);

    $log = ChangeLog::create([
        'loggable_type' => 'PlantingLocation',
        'loggable_id'   => 1,
        'action'        => 'status_changed',
        'created_at'    => $farPast,
    ]);

    expect($log->created_at->diffInSeconds(now()))->toBeLessThan(5);
    expect($log->created_at->year)->not->toBe($farPast->year);
});

it('keeps change log rows after the parent record is hard-deleted via cascade', function () {
    $location = makeLocation();
    $planting = makePlanting($location);

    app(ChangeLogger::class)->record(
        loggableType: 'PlantingLocation',
        loggableId: $location->id,
        action: 'status_changed',
        old: ['status_id' => $this->statusPlanned->id],
        new: ['status_id' => $this->statusActive->id],
    );

    app(ChangeLogger::class)->record(
        loggableType: 'TreePlanting',
        loggableId: $planting->id,
        action: 'status_changed',
        old: ['status' => $this->treeStatusUnverified->id],
        new: ['status' => $this->treeStatusVerified->id],
    );

    $locationId = $location->id;
    $plantingId = $planting->id;

    // Cascades: deleting the location also deletes its tree_plantings row.
    $location->delete();

    expect(PlantingLocation::find($locationId))->toBeNull();
    expect(TreePlanting::find($plantingId))->toBeNull();

    expect(ChangeLog::forEntity('PlantingLocation', $locationId)->count())->toBe(1);
    expect(ChangeLog::forEntity('TreePlanting', $plantingId)->count())->toBe(1);
});
