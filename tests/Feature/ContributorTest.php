<?php

use App\Models\ChangeLog;
use App\Models\Contributor;
use App\Models\Division;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\TreePlanting;
use App\Models\TreePlantingStatus;
use App\Models\TreeType;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $this->treeStatusUnverified = TreePlantingStatus::create(['tree_planting_status' => 'Unverified']);

    $this->division = Division::forceCreate(['LGA_name' => 'Test Division']);
    $this->treeType = TreeType::create(['name' => 'Test Tree', 'latin_name' => 'Testus treeus']);

    $this->owner = User::factory()->create();
    $this->owner->assignRole('Grower');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

function contributorTestLocation(array $attrs = []): PlantingLocation
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

function contributorTestPlanting(PlantingLocation $location, array $attrs = []): TreePlanting
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

it('attaches an existing contributor and logs contributor_attached with the correct name snapshot', function () {
    $location = contributorTestLocation();
    $planting = contributorTestPlanting($location);
    $contributor = Contributor::create(['name' => 'Green Future Foundation']);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-contributors.attach', $planting), [
            'contributor_id' => $contributor->id,
            'note'           => 'Provided seed funding',
        ])
        ->assertRedirect(route('tree-planting-contributors.index', $planting));

    expect($planting->contributors()->where('contributor_id', $contributor->id)->exists())->toBeTrue();
    expect($planting->contributors()->first()->pivot->note)->toBe('Provided seed funding');

    $log = ChangeLog::forEntity('TreePlanting', $planting->id)
        ->where('action', 'contributor_attached')
        ->firstOrFail();

    expect($log->old_values)->toBeNull();
    expect($log->new_values)->toBe(['contributor_id' => $contributor->id, 'contributor_name' => 'Green Future Foundation']);
});

it('attaches a brand-new contributor created inline in the same request', function () {
    $location = contributorTestLocation();
    $planting = contributorTestPlanting($location);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-contributors.attach', $planting), [
            'name'    => 'Riverside School',
            'website' => 'https://riversideschool.example.org',
        ])
        ->assertRedirect(route('tree-planting-contributors.index', $planting));

    $contributor = Contributor::where('name', 'Riverside School')->firstOrFail();

    expect($contributor->website)->toBe('https://riversideschool.example.org');
    expect($planting->contributors()->where('contributor_id', $contributor->id)->exists())->toBeTrue();
});

it('rejects attaching the same contributor twice to the same planting', function () {
    $location = contributorTestLocation();
    $planting = contributorTestPlanting($location);
    $contributor = Contributor::create(['name' => 'Green Future Foundation']);

    $planting->contributors()->attach($contributor->id);

    $this->actingAs($this->admin)
        ->post(route('tree-planting-contributors.attach', $planting), [
            'contributor_id' => $contributor->id,
        ])
        ->assertSessionHasErrors(['contributor_id']);

    expect($planting->contributors()->count())->toBe(1);
});

it('returns the same friendly duplicate-attachment error when two requests race past the pre-check', function () {
    $location = contributorTestLocation();
    $planting = contributorTestPlanting($location);
    $contributor = Contributor::create(['name' => 'Green Future Foundation']);

    // Simulates the actual race the fix targets: a second request's
    // insert lands between this request's pre-check exists() query and
    // its own insert, so the pre-check sees "not attached yet" but the
    // DB insert still collides with the unique constraint. DB::listen
    // fires synchronously right after the pre-check's query executes
    // and before the app evaluates its (already-fetched) result, so
    // inserting here from the listener stands in for a second request
    // winning that narrow window.
    $raced = false;
    DB::listen(function ($query) use (&$raced, $planting, $contributor) {
        if ($raced) {
            return;
        }

        if (str_contains($query->sql, 'contributor_tree_planting') && str_contains($query->sql, 'exists')) {
            $raced = true;

            DB::table('contributor_tree_planting')->insert([
                'contributor_id'   => $contributor->id,
                'tree_planting_id' => $planting->id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    });

    $this->actingAs($this->admin)
        ->post(route('tree-planting-contributors.attach', $planting), [
            'contributor_id' => $contributor->id,
        ])
        ->assertSessionHasErrors(['contributor_id']);

    expect($raced)->toBeTrue();
    expect($planting->contributors()->count())->toBe(1);
});

it('detaches a contributor and logs contributor_detached', function () {
    $location = contributorTestLocation();
    $planting = contributorTestPlanting($location);
    $contributor = Contributor::create(['name' => 'Green Future Foundation']);
    $planting->contributors()->attach($contributor->id);

    $this->actingAs($this->admin)
        ->delete(route('tree-planting-contributors.detach', [$planting, $contributor]))
        ->assertRedirect(route('tree-planting-contributors.index', $planting));

    expect($planting->contributors()->where('contributor_id', $contributor->id)->exists())->toBeFalse();

    $log = ChangeLog::forEntity('TreePlanting', $planting->id)
        ->where('action', 'contributor_detached')
        ->firstOrFail();

    expect($log->old_values)->toBe(['contributor_id' => $contributor->id, 'contributor_name' => 'Green Future Foundation']);
    expect($log->new_values)->toBeNull();
});

it('logs contributor_updated with correct old/new values, and a no-op edit logs nothing', function () {
    $contributor = Contributor::create(['name' => 'Green Future Foundation', 'website' => 'https://old.example.org']);

    $this->actingAs($this->admin)
        ->put(route('contributors.update', $contributor), [
            'name'    => 'Green Future Foundation Inc.',
            'website' => 'https://old.example.org',
        ])
        ->assertRedirect(route('contributors.index'));

    $log = ChangeLog::forEntity('Contributor', $contributor->id)
        ->where('action', 'contributor_updated')
        ->firstOrFail();

    expect($log->old_values)->toBe(['name' => 'Green Future Foundation']);
    expect($log->new_values)->toBe(['name' => 'Green Future Foundation Inc.']);

    // No-op edit: resubmit identical values.
    $this->actingAs($this->admin)
        ->put(route('contributors.update', $contributor), [
            'name'    => 'Green Future Foundation Inc.',
            'website' => 'https://old.example.org',
        ])
        ->assertRedirect(route('contributors.index'));

    expect(ChangeLog::forEntity('Contributor', $contributor->id)->where('action', 'contributor_updated')->count())->toBe(1);
});

it('blocks deleting a contributor that has at least one attachment', function () {
    $location = contributorTestLocation();
    $planting = contributorTestPlanting($location);
    $contributor = Contributor::create(['name' => 'Green Future Foundation']);
    $planting->contributors()->attach($contributor->id);

    $this->actingAs($this->admin)
        ->delete(route('contributors.destroy', $contributor))
        ->assertRedirect(route('contributors.index'));

    expect(Contributor::find($contributor->id))->not->toBeNull();
});

it('cascades pivot rows when the TreePlanting is deleted, contrasting with the Contributor-side RESTRICT', function () {
    $location = contributorTestLocation();
    $planting = contributorTestPlanting($location);
    $contributor = Contributor::create(['name' => 'Green Future Foundation']);
    $planting->contributors()->attach($contributor->id);

    // Deleting the TreePlanting side cascades — the attachment is
    // removed along with it.
    $planting->delete();

    expect(TreePlanting::find($planting->id))->toBeNull();
    expect(DB::table('contributor_tree_planting')->where('contributor_id', $contributor->id)->count())->toBe(0);

    // Contrast: the Contributor itself is untouched by that cascade —
    // only the pivot row was removed, not the Contributor record.
    expect(Contributor::find($contributor->id))->not->toBeNull();
});

it('renders attached contributors per TreePlanting on the public page alongside the unaffected legacy site-level note', function () {
    $location = contributorTestLocation([
        'contributors' => '<p>Legacy site note about this location.</p>',
    ]);
    $planting = contributorTestPlanting($location);
    $contributor = Contributor::create(['name' => 'Green Future Foundation', 'website' => 'https://gff.example.org']);
    $planting->contributors()->attach($contributor->id);

    $response = $this->get(route('public.planting-locations.show', $location->public_code));

    $response->assertOk();
    $response->assertSee('Green Future Foundation');
    $response->assertSee('Legacy site note about this location.', false);
    $response->assertSee('About This Site');
});
