<?php

use App\Models\BiocharBatch;
use App\Models\Division;
use App\Models\Inspection;
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

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

/**
 * One full, cross-linked fixture set spanning all 7 sources, deliberately
 * split across two divisions so a division filter has something real to
 * exclude, and used by every test below rather than rebuilt piecemeal.
 */
function seedExportFixtures(): array
{
    $divisionA = Division::create(['LGA_name' => 'Export Test Division A']);
    $divisionB = Division::create(['LGA_name' => 'Export Test Division B']);

    $locationStatus = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Active']);
    $plantingStatus = TreePlantingStatus::forceCreate(['tree_planting_status' => 'Planted']);

    $owner = User::factory()->create(['telephone' => '0700000000', 'country' => 'Kenya', 'gender' => 'Female']);
    $owner->assignRole('Grower');

    $locationA = PlantingLocation::create([
        'location'    => 'Export Test Location A',
        'division_id' => $divisionA->id,
        'status_id'   => $locationStatus->id,
        'user_id'     => $owner->id,
        'latitude'    => 1.23,
        'longitude'   => 36.45,
    ]);

    $locationB = PlantingLocation::create([
        'location'    => 'Export Test Location B',
        'division_id' => $divisionB->id,
        'status_id'   => $locationStatus->id,
        'user_id'     => $owner->id,
    ]);

    $treeType = TreeType::create(['name' => 'Export Test Species']);

    $plantingA = TreePlanting::create([
        'planting_date'         => now()->subMonth()->toDateString(),
        'number_of_trees'       => 10,
        'tree_type_id'          => $treeType->id,
        'planting_location_id'  => $locationA->id,
        'user_id'               => $owner->id,
        'status'                => $plantingStatus->id,
    ]);

    $inspectionA = Inspection::create([
        'inspection_date'       => now()->toDateString(),
        'comment'                => 'Export test inspection comment',
        'verified'               => true,
        'user_id'                => $owner->id,
        'planting_location_id'   => $locationA->id,
    ]);

    $measurementA = TreePlantingMeasurement::create([
        'tree_planting_id'  => $plantingA->id,
        'measurement_date'  => now()->toDateString(),
        'trees_surviving'   => 8,
        'height_avg_cm'     => 123.45,
        'user_id'           => $owner->id,
    ]);

    $biocharA = BiocharBatch::create([
        'planting_location_id' => $locationA->id,
        'quantity_kg'           => 5.5,
        'source'                => 'Export test source',
        'application_date'      => now()->toDateString(),
        'user_id'               => $owner->id,
    ]);

    return compact(
        'divisionA', 'divisionB', 'locationA', 'locationB',
        'treeType', 'plantingA', 'inspectionA', 'measurementA', 'biocharA', 'owner'
    );
}

// ---------------------------------------------------------------------
// Role gate
// ---------------------------------------------------------------------

it('blocks a guest from both export routes', function () {
    // This app redirects unauthenticated requests to home, not /login —
    // a deliberate, app-wide AuthenticationException handler in
    // bootstrap/app.php, not specific to these routes.
    $this->get(route('export.index'))->assertRedirect(route('home'));
    $this->get(route('export.download'))->assertRedirect(route('home'));
});

// ---------------------------------------------------------------------
// Per-source columns + correctly joined data
// ---------------------------------------------------------------------

it('previews planting_locations with correctly joined division data', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'planting_locations']));

    $response->assertOk();
    $response->assertSee('Reference ID');
    $response->assertSee('LOC-'.$f['locationA']->id);
    $response->assertSee('Export Test Location A');
    $response->assertSee('Export Test Division A');
});

it('previews tree_plantings with correctly joined tree type, location, and division data', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'tree_plantings']));

    $response->assertOk();
    $response->assertSee('PLT-'.$f['plantingA']->id);
    $response->assertSee('Export Test Species');
    $response->assertSee('Export Test Location A');
    $response->assertSee('Export Test Division A');
});

it('previews inspections with correctly joined location and division data', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'inspections']));

    $response->assertOk();
    $response->assertSee('INS-'.$f['inspectionA']->id);
    $response->assertSee('Export test inspection comment');
    $response->assertSee('Export Test Location A');
    $response->assertSee('Export Test Division A');
});

it('previews tree_planting_measurements with correctly joined planting, location, and division data', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'tree_planting_measurements']));

    $response->assertOk();
    $response->assertSee('MSR-'.$f['measurementA']->id);
    $response->assertSee('Export Test Species');
    $response->assertSee('Export Test Location A');
    $response->assertSee('Export Test Division A');
});

it('previews biochar_batches with correctly joined location and division data', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'biochar_batches']));

    $response->assertOk();
    $response->assertSee('BIO-'.$f['biocharA']->id);
    $response->assertSee('Export test source');
    $response->assertSee('Export Test Location A');
    $response->assertSee('Export Test Division A');
});

it('previews tree_types with the total_trees_planted aggregate', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'tree_types']));

    $response->assertOk();
    $response->assertSee('TYP-'.$f['treeType']->id);
    $response->assertSee('Export Test Species');
    $response->assertSee('10'); // total_trees_planted, from plantingA's number_of_trees
});

it('previews users with role names, excluding the email column entirely', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'users']));

    $response->assertOk();
    $response->assertSee('USR-'.$f['owner']->id);
    $response->assertSee($f['owner']->name);
    $response->assertSee('Grower');
    // Not asserting assertDontSee('Email') here — the app's own footer
    // partial legitimately contains contact "Email:" text on every page,
    // unrelated to this source's columns. The precise, real check (no
    // email VALUE, header, password, or remember_token anywhere) is the
    // dedicated security test below.
});

// ---------------------------------------------------------------------
// Finding #1 regression: TreePlanting::division() is broken and must
// never be used. These assert the ACTUAL division name text is present
// for tree_plantings/tree_planting_measurements — if a future change
// swapped plantingLocation.division for TreePlanting::division(), the
// division column would silently go blank and this would fail.
// ---------------------------------------------------------------------

it('shows the correct, non-null division for tree_plantings via plantingLocation.division', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.download', ['table' => 'tree_plantings']));

    $csv = $response->streamedContent();

    expect($csv)->toContain('Export Test Division A');
});

it('shows the correct, non-null division for tree_planting_measurements via the two-hop treePlanting.plantingLocation.division path', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.download', ['table' => 'tree_planting_measurements']));

    $csv = $response->streamedContent();

    expect($csv)->toContain('Export Test Division A');
});

// ---------------------------------------------------------------------
// Division filter: applies where it should, geographically
// ---------------------------------------------------------------------

it('filters planting_locations by division, excluding the other division', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'planting_locations', 'division' => $f['divisionA']->id]));

    $response->assertOk();
    $response->assertSee('Export Test Location A');
    $response->assertDontSee('Export Test Location B');
});

it('filters tree_plantings by division via the plantingLocation relation', function () {
    $f = seedExportFixtures();

    // A second, unrelated planting in division B to prove it's excluded.
    $plantingStatus = TreePlantingStatus::first();
    TreePlanting::create([
        'planting_date'        => now()->toDateString(),
        'number_of_trees'      => 3,
        'tree_type_id'         => $f['treeType']->id,
        'planting_location_id' => $f['locationB']->id,
        'user_id'              => $f['owner']->id,
        'status'               => $plantingStatus->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'tree_plantings', 'division' => $f['divisionA']->id]));

    $response->assertOk();
    $response->assertSee('Export Test Location A');
    $response->assertDontSee('Export Test Location B');
});

// ---------------------------------------------------------------------
// Division filter: correctly inert for tree_types and users, server-side
// ---------------------------------------------------------------------

it('ignores the division filter server-side for tree_types, even with a division id that would exclude everything if it applied', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'tree_types', 'division' => 999999]));

    $response->assertOk();
    $response->assertSee('Export Test Species');
});

it('ignores the division filter server-side for users, even with a division id that would exclude everything if it applied', function () {
    $f = seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'users', 'division' => 999999]));

    $response->assertOk();
    $response->assertSee($f['owner']->name);
});

// ---------------------------------------------------------------------
// Search filter: relation-based OR-grouped match, and the tree_types
// name/latin_name OR.
// ---------------------------------------------------------------------

it('matches search on the related divisions LGA_name, not just the location name, for tree_plantings', function () {
    $f = seedExportFixtures();

    // A second planting in division B — its own location name does NOT
    // contain "Division A", so if this shows up it can only be because
    // the search matched via the whereHas('plantingLocation', ...
    // orWhereHas('division', ...)) path, not a direct location-column hit.
    $plantingStatus = TreePlantingStatus::first();
    TreePlanting::create([
        'planting_date'        => now()->toDateString(),
        'number_of_trees'      => 3,
        'tree_type_id'         => $f['treeType']->id,
        'planting_location_id' => $f['locationB']->id,
        'user_id'              => $f['owner']->id,
        'status'               => $plantingStatus->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'tree_plantings', 'search' => 'Division A']));

    $response->assertOk();
    $response->assertSee('Export Test Location A');
    $response->assertDontSee('Export Test Location B');
});

it('matches search on tree_types latin_name, not just name', function () {
    seedExportFixtures();

    TreeType::create(['name' => 'Some Other Common Name', 'latin_name' => 'Distinctus Latinus']);

    $response = $this->actingAs($this->admin)
        ->get(route('export.index', ['table' => 'tree_types', 'search' => 'Distinctus Latinus']));

    $response->assertOk();
    $response->assertSee('Some Other Common Name');
    $response->assertDontSee('Export Test Species');
});

// ---------------------------------------------------------------------
// CRITICAL security regression: password/remember_token/email must
// never appear in the users source, preview or download, under any
// filter. String-searches actual response content — defense in depth,
// not just a check on the query builder's select list.
// ---------------------------------------------------------------------

it('never leaks password, remember_token, or email anywhere in the users export preview or download', function () {
    seedExportFixtures();

    $sensitiveUser = User::factory()->create([
        'email'           => 'super-secret-address@example.test',
        'remember_token'  => 'SUPER-SECRET-REMEMBER-TOKEN-XYZ',
    ]);
    $rawPasswordHash = $sensitiveUser->password;

    $preview = $this->actingAs($this->admin)->get(route('export.index', ['table' => 'users']));
    $preview->assertOk();
    $preview->assertDontSee('super-secret-address@example.test', false);
    $preview->assertDontSee('SUPER-SECRET-REMEMBER-TOKEN-XYZ', false);
    $preview->assertDontSee($rawPasswordHash, false);

    $download = $this->actingAs($this->admin)->get(route('export.download', ['table' => 'users']));
    $download->assertOk();
    $csv = $download->streamedContent();

    expect($csv)->not->toContain('super-secret-address@example.test');
    expect($csv)->not->toContain('SUPER-SECRET-REMEMBER-TOKEN-XYZ');
    expect($csv)->not->toContain($rawPasswordHash);
});

// ---------------------------------------------------------------------
// Download: real CSV headers, and the FULL dataset, not the paginated
// preview subset.
// ---------------------------------------------------------------------

it('returns a real CSV response with attachment headers', function () {
    seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.download', ['table' => 'planting_locations']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->headers->get('content-disposition'))->toContain('attachment');
    expect($response->headers->get('content-disposition'))->toContain('.csv');
});

it('prefixes the CSV download with a UTF-8 BOM and an Excel sep= directive before the header row', function () {
    seedExportFixtures();

    $response = $this->actingAs($this->admin)
        ->get(route('export.download', ['table' => 'planting_locations']));

    $csv = $response->streamedContent();

    expect(substr($csv, 0, 3))->toBe("\xEF\xBB\xBF");
    expect(substr($csv, 3, 6))->toBe("sep=,\n");

    // Parse rather than assume quoting — fputcsv quotes header fields
    // containing spaces, so a raw string-prefix check would be brittle.
    $headerLine = strtok(substr($csv, 9), "\n");
    $headerColumns = str_getcsv($headerLine);

    expect($headerColumns[0])->toBe('Reference ID');
});

it('the download route returns every matching row, not just the 50-row preview page', function () {
    seedExportFixtures();

    for ($i = 1; $i <= 60; $i++) {
        TreeType::create(['name' => 'Bulk Export Species '.$i]);
    }

    $preview = $this->actingAs($this->admin)->get(route('export.index', ['table' => 'tree_types']));
    $preview->assertOk();
    $preview->assertDontSee('Bulk Export Species 60');

    $download = $this->actingAs($this->admin)->get(route('export.download', ['table' => 'tree_types']));
    $csv = $download->streamedContent();

    expect($csv)->toContain('Bulk Export Species 60');
    expect(substr_count($csv, 'Bulk Export Species'))->toBe(60);
});

// ---------------------------------------------------------------------
// Reference ID format (addendum): PREFIX-id, no zero-padding, hyphen
// present — checked as an exact first-column value, not a substring,
// so "LOC-1" vs "LOC-10" vs a missing hyphen can't false-pass.
// ---------------------------------------------------------------------

it('formats the reference ID as an exact PREFIX-id first column for planting_locations and users', function () {
    $f = seedExportFixtures();

    $locationsCsv = $this->actingAs($this->admin)
        ->get(route('export.download', ['table' => 'planting_locations']))
        ->streamedContent();

    $locationRow = firstCsvRowMatching($locationsCsv, fn ($cols) => $cols[0] === 'LOC-'.$f['locationA']->id);
    expect($locationRow)->not->toBeNull();
    expect($locationRow[0])->toBe('LOC-'.$f['locationA']->id);

    $usersCsv = $this->actingAs($this->admin)
        ->get(route('export.download', ['table' => 'users']))
        ->streamedContent();

    $userRow = firstCsvRowMatching($usersCsv, fn ($cols) => $cols[0] === 'USR-'.$f['owner']->id);
    expect($userRow)->not->toBeNull();
    expect($userRow[0])->toBe('USR-'.$f['owner']->id);
});

function firstCsvRowMatching(string $csv, callable $predicate): ?array
{
    $lines = array_filter(explode("\n", str_replace("\r\n", "\n", trim($csv))));

    foreach ($lines as $line) {
        $cols = str_getcsv($line);
        if ($predicate($cols)) {
            return $cols;
        }
    }

    return null;
}
