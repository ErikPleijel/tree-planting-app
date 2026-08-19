<?php

use App\Models\BiocharBatch;
use App\Models\ChangeLog;
use App\Models\Division;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\TreePlanting;
use App\Models\TreePlantingStatus;
use App\Models\TreeType;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    // PlantingLocationStatus/Division have no $fillable (pre-existing,
    // out of scope), so forceCreate() is used — same workaround as the
    // Phase 1/2 test suites.
    $this->statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $this->treeStatusUnverified = TreePlantingStatus::create(['tree_planting_status' => 'Unverified']);

    $this->division = Division::forceCreate(['LGA_name' => 'Test Division']);
    $this->treeType = TreeType::create(['name' => 'Test Tree', 'latin_name' => 'Testus treeus']);

    $this->owner = User::factory()->create();
    $this->owner->assignRole('Grower');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

function biocharTestLocation(array $attrs = []): PlantingLocation
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

function biocharTestPlanting(PlantingLocation $location, array $attrs = []): TreePlanting
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

it('derives planting_location_id automatically when only tree_planting_id is provided', function () {
    $location = biocharTestLocation();
    $planting = biocharTestPlanting($location);

    $this->actingAs($this->admin)
        ->post(route('biochar-batches.store'), [
            'tree_planting_id' => $planting->id,
            'quantity_kg'      => 12.5,
            'source'           => 'coconut husk',
            'application_date' => now()->toDateString(),
        ])
        ->assertRedirect(route('biochar-batches.index', $location->id));

    $batch = BiocharBatch::where('tree_planting_id', $planting->id)->firstOrFail();
    expect($batch->planting_location_id)->toBe($location->id);
});

it('accepts a batch linked only to a planting_location with no specific tree_planting', function () {
    $location = biocharTestLocation();

    $this->actingAs($this->admin)
        ->post(route('biochar-batches.store'), [
            'planting_location_id' => $location->id,
            'quantity_kg'          => 5,
            'source'               => 'invasive species clearing',
            'application_date'     => now()->toDateString(),
        ])
        ->assertRedirect(route('biochar-batches.index', $location->id));

    $batch = BiocharBatch::where('planting_location_id', $location->id)->firstOrFail();
    expect($batch->tree_planting_id)->toBeNull();
});

it('rejects a batch with neither planting_location_id nor tree_planting_id', function () {
    $this->actingAs($this->admin)
        ->post(route('biochar-batches.store'), [
            'quantity_kg'      => 5,
            'source'           => 'unknown',
            'application_date' => now()->toDateString(),
        ])
        ->assertSessionHasErrors(['planting_location_id']);

    expect(BiocharBatch::count())->toBe(0);
});

it('nulls out tree_planting_id when the parent planting is deleted, unlike Phase 2 measurements which cascade-delete', function () {
    $location = biocharTestLocation();
    $planting = biocharTestPlanting($location);

    $this->actingAs($this->admin)
        ->post(route('biochar-batches.store'), [
            'tree_planting_id' => $planting->id,
            'quantity_kg'      => 8,
            'source'           => 'rice husk',
            'application_date' => now()->toDateString(),
        ]);

    $batch   = BiocharBatch::where('tree_planting_id', $planting->id)->firstOrFail();
    $batchId = $batch->id;

    $planting->delete();

    expect(TreePlanting::find($planting->id))->toBeNull();

    // Contrast with tree_planting_measurements (Phase 2), which cascades
    // and would leave no row here at all — biochar_batches survives with
    // tree_planting_id nulled out instead.
    $batch = BiocharBatch::find($batchId);
    expect($batch)->not->toBeNull();
    expect($batch->tree_planting_id)->toBeNull();
    expect($batch->planting_location_id)->toBe($location->id);
});

it('logs a change entry with correct old/new values when quantity_kg is edited', function () {
    $location = biocharTestLocation();

    $this->actingAs($this->admin)
        ->post(route('biochar-batches.store'), [
            'planting_location_id' => $location->id,
            'quantity_kg'          => 10,
            'source'               => 'coconut husk',
            'application_date'     => now()->toDateString(),
        ]);

    $batch = BiocharBatch::where('planting_location_id', $location->id)->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('biochar-batches.update', $batch), [
            'quantity_kg'      => 15,
            'source'           => 'coconut husk',
            'application_date' => $batch->application_date->toDateString(),
        ])
        ->assertRedirect(route('biochar-batches.index', $location->id));

    $log = ChangeLog::forEntity('BiocharBatch', $batch->id)
        ->where('action', 'batch_updated')
        ->firstOrFail();

    expect((float) $log->old_values['quantity_kg'])->toBe(10.0);
    expect((float) $log->new_values['quantity_kg'])->toBe(15.0);
    expect($log->old_values)->not->toHaveKey('source'); // unchanged field not logged
});
