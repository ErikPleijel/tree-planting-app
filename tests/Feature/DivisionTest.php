<?php

use App\Models\Division;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('SuperAdmin');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

it('lets a SuperAdmin view, create, edit, and delete an unreferenced division', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('divisions.index'))
        ->assertOk();

    $this->actingAs($this->superAdmin)
        ->get(route('divisions.create'))
        ->assertOk();

    $this->actingAs($this->superAdmin)
        ->post(route('divisions.store'), [
            'LGA_name'  => 'Test Division',
            'latitude'  => 1.234,
            'longitude' => 36.789,
        ])
        ->assertRedirect(route('divisions.index'));

    $division = Division::where('LGA_name', 'Test Division')->firstOrFail();

    $this->actingAs($this->superAdmin)
        ->get(route('divisions.edit', $division))
        ->assertOk();

    $this->actingAs($this->superAdmin)
        ->put(route('divisions.update', $division), [
            'LGA_name' => 'Updated Division',
        ])
        ->assertRedirect(route('divisions.index'));

    expect($division->fresh()->LGA_name)->toBe('Updated Division');

    $this->actingAs($this->superAdmin)
        ->delete(route('divisions.destroy', $division))
        ->assertRedirect(route('divisions.index'));

    expect(Division::find($division->id))->toBeNull();
});

it('blocks an Admin from every divisions route, including index', function () {
    $division = Division::create(['LGA_name' => 'Blocked Division']);

    $this->actingAs($this->admin)
        ->get(route('divisions.index'))
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->get(route('divisions.create'))
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->post(route('divisions.store'), ['LGA_name' => 'Should Not Be Created'])
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->get(route('divisions.edit', $division))
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->put(route('divisions.update', $division), ['LGA_name' => 'Should Not Update'])
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->delete(route('divisions.destroy', $division))
        ->assertForbidden();

    expect(Division::where('LGA_name', 'Should Not Be Created')->exists())->toBeFalse();
    expect($division->fresh()->LGA_name)->toBe('Blocked Division');
    expect(Division::find($division->id))->not->toBeNull();
});

it('blocks deleting a division that has at least one planting location, and does not cascade the delete', function () {
    $status = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $division = Division::create(['LGA_name' => 'In Use Division']);
    $owner = User::factory()->create();
    $owner->assignRole('Grower');

    $location = PlantingLocation::create([
        'location'    => 'Guarded Location',
        'division_id' => $division->id,
        'status_id'   => $status->id,
        'user_id'     => $owner->id,
    ]);

    $this->actingAs($this->superAdmin)
        ->delete(route('divisions.destroy', $division))
        ->assertRedirect(route('divisions.index'))
        ->assertSessionHas('error');

    // The whole point of the guard: division_id has an onDelete('cascade')
    // FK, so this isn't just about the error message — verify nothing was
    // actually deleted at the DB level.
    expect(Division::find($division->id))->not->toBeNull();
    expect(PlantingLocation::find($location->id))->not->toBeNull();
});

it('deletes a division with no planting locations', function () {
    $division = Division::create(['LGA_name' => 'Empty Division']);

    $this->actingAs($this->superAdmin)
        ->delete(route('divisions.destroy', $division))
        ->assertRedirect(route('divisions.index'))
        ->assertSessionHas('success');

    expect(Division::find($division->id))->toBeNull();
});

it('rejects out-of-range latitude and longitude values', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('divisions.store'), [
            'LGA_name'  => 'Bad Coordinates',
            'latitude'  => 95,
            'longitude' => 200,
        ])
        ->assertSessionHasErrors(['latitude', 'longitude']);

    expect(Division::where('LGA_name', 'Bad Coordinates')->exists())->toBeFalse();
});
