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

it('logs a coordinates_set change entry with capture method and accuracy when creating a location with coordinates', function () {
    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'            => 'New Test Location',
            'division_id'         => $this->division->id,
            'status_id'           => $this->statusPlanned->id,
            'latitude'            => 9.123456,
            'longitude'           => 7.654321,
            'capture_method'      => 'gps_button',
            'gps_accuracy_meters' => 12.5,
        ])
        ->assertRedirect();

    $location = PlantingLocation::where('location', 'New Test Location')->firstOrFail();

    $log = ChangeLog::forEntity('PlantingLocation', $location->id)
        ->where('action', 'coordinates_set')
        ->firstOrFail();

    expect($log->old_values)->toBeNull();
    expect($log->new_values['capture_method'])->toBe('gps_button');
    expect((float) $log->new_values['accuracy_meters'])->toBe(12.5);
    expect((float) $log->new_values['latitude'])->toBe(9.123456);
    expect((float) $log->new_values['longitude'])->toBe(7.654321);
});

it('rejects out-of-range latitude and longitude values on create', function () {
    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'    => 'Out Of Range Location',
            'division_id' => $this->division->id,
            'status_id'   => $this->statusPlanned->id,
            'latitude'    => 200,
            'longitude'   => -200,
        ])
        ->assertSessionHasErrors(['latitude', 'longitude']);

    expect(PlantingLocation::where('location', 'Out Of Range Location')->exists())->toBeFalse();
});

it('does not log a coordinates_set entry when creating a location without coordinates', function () {
    $this->actingAs($this->admin)
        ->post(route('planting-locations.store'), [
            'location'    => 'No Coordinates Location',
            'division_id' => $this->division->id,
            'status_id'   => $this->statusPlanned->id,
        ])
        ->assertRedirect();

    $location = PlantingLocation::where('location', 'No Coordinates Location')->firstOrFail();

    expect(ChangeLog::forEntity('PlantingLocation', $location->id)->where('action', 'coordinates_set')->count())->toBe(0);
});

it('logs coordinates_updated with capture_method/accuracy_meters in new_values only, not old_values', function () {
    $location = PlantingLocation::create([
        'location'    => 'Existing Location',
        'division_id' => $this->division->id,
        'status_id'   => $this->statusPlanned->id,
        'user_id'     => $this->admin->id,
        'latitude'    => 1.0,
        'longitude'   => 1.0,
    ]);

    $this->actingAs($this->admin)
        ->put(route('planting-locations.update', $location), [
            'location'            => $location->location,
            'division_id'         => $this->division->id,
            'status_id'           => $this->statusPlanned->id,
            'latitude'            => 2.0,
            'longitude'           => 2.0,
            'capture_method'      => 'manual',
            'gps_accuracy_meters' => '',
        ])
        ->assertRedirect(route('planting-locations.show', $location));

    $log = ChangeLog::forEntity('PlantingLocation', $location->id)
        ->where('action', 'coordinates_updated')
        ->firstOrFail();

    expect($log->old_values['latitude'])->toEqual(1.0);
    expect($log->old_values['longitude'])->toEqual(1.0);
    expect($log->old_values)->not->toHaveKey('capture_method');
    expect($log->old_values)->not->toHaveKey('accuracy_meters');

    expect($log->new_values['capture_method'])->toBe('manual');
    expect($log->new_values)->toHaveKey('accuracy_meters');
    expect($log->new_values['accuracy_meters'])->toBeNull();
});
