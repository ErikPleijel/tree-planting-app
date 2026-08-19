<?php

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

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

it('rejects wood_density_kg_m3 without a source_reference', function () {
    $this->actingAs($this->admin)
        ->post(route('tree-types.store'), [
            'name'               => 'Test Tree',
            'wood_density_kg_m3' => 550,
        ])
        ->assertSessionHasErrors(['source_reference']);

    expect(TreeType::where('name', 'Test Tree')->exists())->toBeFalse();
});

it('accepts wood_density_kg_m3 and carbon_fraction when a source_reference is provided', function () {
    $this->actingAs($this->admin)
        ->post(route('tree-types.store'), [
            'name'               => 'Test Tree',
            'wood_density_kg_m3' => 550,
            'carbon_fraction'    => 0.47,
            'source_reference'   => 'IPCC 2006 Guidelines, Vol 4, Table 4.14',
        ])
        ->assertRedirect(route('tree-types.index'));

    $treeType = TreeType::where('name', 'Test Tree')->firstOrFail();
    expect((float) $treeType->wood_density_kg_m3)->toBe(550.0);
    expect((float) $treeType->carbon_fraction)->toBe(0.47);
    expect($treeType->source_reference)->toBe('IPCC 2006 Guidelines, Vol 4, Table 4.14');
});

it('renders wood_density_kg_m3, carbon_fraction, and source_reference on the public planting-location page', function () {
    $statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $treeStatus    = TreePlantingStatus::create(['tree_planting_status' => 'Unverified']);
    $division      = Division::forceCreate(['LGA_name' => 'Test Division']);

    $treeType = TreeType::create([
        'name'               => 'Public Test Tree',
        'wood_density_kg_m3' => 620.5,
        'carbon_fraction'    => 0.471,
        'source_reference'   => 'Test Citation Source XYZ',
    ]);

    $owner = User::factory()->create();
    $owner->assignRole('Grower');

    $location = PlantingLocation::create([
        'location'    => 'Public Test Location',
        'division_id' => $division->id,
        'status_id'   => $statusPlanned->id,
        'user_id'     => $owner->id,
    ]);

    TreePlanting::create([
        'planting_date'         => now()->subMonth()->toDateString(),
        'number_of_trees'       => 5,
        'tree_type_id'          => $treeType->id,
        'planting_location_id'  => $location->id,
        'user_id'               => $owner->id,
        'status'                => $treeStatus->id,
        'status_updated_by'     => $owner->id,
    ]);

    $response = $this->get(route('public.planting-locations.show', $location->public_code));

    $response->assertOk();
    $response->assertSee('620.5');
    $response->assertSee('0.471');
    $response->assertSee('Test Citation Source XYZ');
});
