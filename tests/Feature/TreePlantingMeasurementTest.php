<?php

use App\Models\ChangeLog;
use App\Models\Division;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\TreePlanting;
use App\Models\TreePlantingMeasurement;
use App\Models\TreePlantingStatus;
use App\Models\TreeType;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    // PlantingLocationStatus/Division have no $fillable (pre-existing, out
    // of scope), so forceCreate() is used instead of create() — same
    // workaround as the Phase 1 ChangeLogTest.
    $this->statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $this->treeStatusUnverified = TreePlantingStatus::create(['tree_planting_status' => 'Unverified']);

    $this->division = Division::forceCreate(['LGA_name' => 'Test Division']);
    $this->treeType = TreeType::create(['name' => 'Test Tree', 'latin_name' => 'Testus treeus']);

    $this->owner = User::factory()->create();
    $this->owner->assignRole('Grower');

    $this->grower = User::factory()->create();
    $this->grower->assignRole('Grower');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

function measurementTestLocation(array $attrs = []): PlantingLocation
{
    return PlantingLocation::create(array_merge([
        'location'    => 'Test Location '.uniqid(),
        'division_id' => test()->division->id,
        'status_id'   => test()->statusPlanned->id,
        'user_id'     => test()->owner->id,
        'latitude'    => 9.0,
        'longitude'   => 7.0,
    ], $attrs));
}

function measurementTestPlanting(PlantingLocation $location, array $attrs = []): TreePlanting
{
    return TreePlanting::create(array_merge([
        'planting_date'     => now()->subYear()->toDateString(),
        'number_of_trees'   => 10,
        'tree_type_id'      => test()->treeType->id,
        'planting_location_id' => $location->id,
        'user_id'           => test()->owner->id,
        'status'            => test()->treeStatusUnverified->id,
        'status_updated_by' => test()->owner->id,
    ], $attrs));
}

it('links a measurement to the specific tree planting it was recorded for', function () {
    $location  = measurementTestLocation();
    $plantingA = measurementTestPlanting($location);
    $plantingB = measurementTestPlanting($location);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-measurements.store', $plantingA), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 8,
        ])
        ->assertRedirect(route('tree-planting-measurements.index', $plantingA));

    expect(TreePlantingMeasurement::where('tree_planting_id', $plantingA->id)->count())->toBe(1);
    expect(TreePlantingMeasurement::where('tree_planting_id', $plantingB->id)->count())->toBe(0);
});

it('rejects trees_surviving greater than the plantings number_of_trees', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location, ['number_of_trees' => 5]);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 6,
        ])
        ->assertSessionHasErrors(['trees_surviving']);

    expect(TreePlantingMeasurement::where('tree_planting_id', $planting->id)->count())->toBe(0);
});

it('rejects a measurement_date before the plantings planting_date', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location, ['planting_date' => now()->subMonths(2)->toDateString()]);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->subMonths(3)->toDateString(),
            'trees_surviving'  => 5,
        ])
        ->assertSessionHasErrors(['measurement_date']);

    expect(TreePlantingMeasurement::where('tree_planting_id', $planting->id)->count())->toBe(0);
});

it('lets a grower create a measurement but rejects them from verifying one', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location);

    $this->actingAs($this->grower)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 5,
        ])
        ->assertRedirect(route('tree-planting-measurements.index', $planting));

    $measurement = TreePlantingMeasurement::where('tree_planting_id', $planting->id)->firstOrFail();

    $this->actingAs($this->grower)
        ->patch(route('tree-planting-measurements.verify', $measurement))
        ->assertForbidden();

    expect($measurement->fresh()->verified_by_user_id)->toBeNull();
});

it('does not allow a privileged user to verify a measurement they recorded themselves', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 5,
        ]);

    $measurement = TreePlantingMeasurement::where('tree_planting_id', $planting->id)->firstOrFail();
    expect($measurement->user_id)->toBe($this->admin->id);

    $this->actingAs($this->admin)
        ->patch(route('tree-planting-measurements.verify', $measurement))
        ->assertSessionHasErrors(['verify']);

    expect($measurement->fresh()->verified_by_user_id)->toBeNull();
});

it('sets verified_by_user_id and verified_at and writes a change log when verified', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location);

    $this->actingAs($this->grower)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 5,
        ]);

    $measurement = TreePlantingMeasurement::where('tree_planting_id', $planting->id)->firstOrFail();

    $this->actingAs($this->admin)
        ->patch(route('tree-planting-measurements.verify', $measurement))
        ->assertRedirect(route('tree-planting-measurements.index', $planting));

    $measurement->refresh();

    expect($measurement->verified_by_user_id)->toBe($this->admin->id);
    expect($measurement->verified_at)->not->toBeNull();

    $logs = ChangeLog::forEntity('TreePlantingMeasurement', $measurement->id)
        ->where('action', 'measurement_verified')
        ->get();

    expect($logs)->toHaveCount(1);
    expect($logs->first()->changed_by)->toBe($this->admin->id);
    expect($logs->first()->new_values['verified_by_user_id'])->toBe($this->admin->id);
});

it('logs only the fields that actually changed when editing a measurement', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location, ['number_of_trees' => 20]);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 15,
        ]);

    $measurement  = TreePlantingMeasurement::where('tree_planting_id', $planting->id)->firstOrFail();
    $originalDate = $measurement->measurement_date->toDateString();

    $this->actingAs($this->admin)
        ->put(route('tree-planting-measurements.update', $measurement), [
            'measurement_date' => $originalDate,     // unchanged
            'trees_surviving'  => 12,                // changed
            'notes'            => 'Some dieback observed', // changed (was null)
        ])
        ->assertRedirect(route('tree-planting-measurements.index', $planting));

    $log = ChangeLog::forEntity('TreePlantingMeasurement', $measurement->id)
        ->where('action', 'measurement_updated')
        ->firstOrFail();

    expect($log->old_values)->toBe(['trees_surviving' => 15, 'notes' => null]);
    expect($log->new_values)->toBe(['trees_surviving' => 12, 'notes' => 'Some dieback observed']);

    // Unverified before and after — no regression from the verification-reset fix.
    expect($measurement->fresh()->verified_by_user_id)->toBeNull();
    expect(ChangeLog::forEntity('TreePlantingMeasurement', $measurement->id)->where('action', 'measurement_verification_reset')->count())->toBe(0);
});

it('editing a verified measurement resets its verification and logs a separate entry', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location, ['number_of_trees' => 20]);

    $this->actingAs($this->grower)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 15,
        ]);

    $measurement = TreePlantingMeasurement::where('tree_planting_id', $planting->id)->firstOrFail();

    $this->actingAs($this->admin)
        ->patch(route('tree-planting-measurements.verify', $measurement))
        ->assertRedirect(route('tree-planting-measurements.index', $planting));

    $measurement->refresh();
    expect($measurement->verified_by_user_id)->toBe($this->admin->id);
    expect($measurement->verified_at)->not->toBeNull();

    $originalDate = $measurement->measurement_date->toDateString();

    $this->actingAs($this->admin)
        ->put(route('tree-planting-measurements.update', $measurement), [
            'measurement_date' => $originalDate,
            'trees_surviving'  => 10, // changed from 15
        ])
        ->assertRedirect(route('tree-planting-measurements.index', $planting));

    $measurement->refresh();

    expect($measurement->verified_by_user_id)->toBeNull();
    expect($measurement->verified_at)->toBeNull();

    $updatedLog = ChangeLog::forEntity('TreePlantingMeasurement', $measurement->id)
        ->where('action', 'measurement_updated')
        ->firstOrFail();
    expect($updatedLog->old_values)->toBe(['trees_surviving' => 15]);
    expect($updatedLog->new_values)->toBe(['trees_surviving' => 10]);

    $resetLog = ChangeLog::forEntity('TreePlantingMeasurement', $measurement->id)
        ->where('action', 'measurement_verification_reset')
        ->firstOrFail();
    expect($resetLog->old_values['verified_by_user_id'])->toBe($this->admin->id);
    expect($resetLog->new_values)->toBe(['verified_by_user_id' => null, 'verified_at' => null]);
});

it('does not reset verification or log anything when a no-op edit re-submits the same values', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location, ['number_of_trees' => 20]);

    $this->actingAs($this->grower)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 15,
        ]);

    $measurement = TreePlantingMeasurement::where('tree_planting_id', $planting->id)->firstOrFail();

    $this->actingAs($this->admin)
        ->patch(route('tree-planting-measurements.verify', $measurement))
        ->assertRedirect(route('tree-planting-measurements.index', $planting));

    $measurement->refresh();
    $originalDate = $measurement->measurement_date->toDateString();

    $this->actingAs($this->admin)
        ->put(route('tree-planting-measurements.update', $measurement), [
            'measurement_date' => $originalDate,
            'trees_surviving'  => 15, // resubmitted unchanged
        ])
        ->assertRedirect(route('tree-planting-measurements.index', $planting));

    $measurement->refresh();

    expect($measurement->verified_by_user_id)->toBe($this->admin->id);
    expect($measurement->verified_at)->not->toBeNull();

    expect(ChangeLog::forEntity('TreePlantingMeasurement', $measurement->id)->where('action', 'measurement_updated')->count())->toBe(0);
    expect(ChangeLog::forEntity('TreePlantingMeasurement', $measurement->id)->where('action', 'measurement_verification_reset')->count())->toBe(0);
});

it('cascades measurement deletion when the parent tree planting is deleted', function () {
    $location = measurementTestLocation();
    $planting = measurementTestPlanting($location);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-measurements.store', $planting), [
            'measurement_date' => now()->toDateString(),
            'trees_surviving'  => 5,
        ]);

    $measurement   = TreePlantingMeasurement::where('tree_planting_id', $planting->id)->firstOrFail();
    $measurementId = $measurement->id;

    // Contrast with Phase 1's change_logs, which deliberately does NOT
    // cascade — tree_planting_measurements is the opposite by design.
    $planting->delete();

    expect(TreePlanting::find($planting->id))->toBeNull();
    expect(TreePlantingMeasurement::find($measurementId))->toBeNull();
});
