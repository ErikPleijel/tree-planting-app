<?php

use App\Models\Division;
use App\Models\Picture;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\User;
use App\Services\ExifExtractor;
use Database\Seeders\RolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    Storage::fake('public');

    $this->statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $this->division      = Division::forceCreate(['LGA_name' => 'Test Division']);

    $this->owner = User::factory()->create();
    $this->owner->assignRole('Grower');

    $this->location = PlantingLocation::create([
        'location'    => 'Consent Test Location',
        'division_id' => $this->division->id,
        'status_id'   => $this->statusPlanned->id,
        'user_id'     => $this->owner->id,
    ]);
});

it('rejects a camera-captured photo upload when consent is not confirmed', function () {
    $this->actingAs($this->owner)
        ->post(route('pictures.store'), [
            'image_data'           => 'data:image/jpeg;base64,'.base64_encode('fake-image-bytes'),
            'planting_location_id' => $this->location->id,
            // consent_confirmed deliberately omitted
        ])
        ->assertSessionHasErrors(['consent_confirmed']);

    expect(Picture::where('planting_location_id', $this->location->id)->exists())->toBeFalse();
});

it('creates a camera-captured photo with consent_confirmed_at set when consent is confirmed', function () {
    $this->actingAs($this->owner)
        ->post(route('pictures.store'), [
            'image_data'           => 'data:image/jpeg;base64,'.base64_encode('fake-image-bytes'),
            'planting_location_id' => $this->location->id,
            'consent_confirmed'    => '1',
        ])
        ->assertRedirect();

    $picture = Picture::where('planting_location_id', $this->location->id)->firstOrFail();

    expect($picture->consent_confirmed_at)->not->toBeNull();
    // A tolerance window, not exact-equality, since the timestamp column's
    // storage precision can truncate sub-second fractions.
    expect(abs($picture->consent_confirmed_at->diffInSeconds(now())))->toBeLessThan(5);
});

it('rejects a device-upload photo batch when consent is not confirmed', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($this->owner)
        ->post(route('pictures.upload.store', $this->location), [
            'photos' => [$file],
            // consent_confirmed deliberately omitted
        ])
        ->assertSessionHasErrors(['consent_confirmed']);

    expect(Picture::where('planting_location_id', $this->location->id)->exists())->toBeFalse();
});

it('creates device-uploaded photos with consent_confirmed_at set when consent is confirmed', function () {
    $this->instance(ExifExtractor::class, new class extends ExifExtractor {
        public function extract(string $filePath): array
        {
            return ['captured_at' => null, 'latitude' => null, 'longitude' => null];
        }
    });

    $files = [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')];

    $this->actingAs($this->owner)
        ->post(route('pictures.upload.store', $this->location), [
            'photos'            => $files,
            'consent_confirmed' => '1',
        ])
        ->assertRedirect(route('planting-locations.show', $this->location));

    $pictures = Picture::where('planting_location_id', $this->location->id)->get();

    expect($pictures)->toHaveCount(2);
    $pictures->each(function (Picture $picture) {
        expect($picture->consent_confirmed_at)->not->toBeNull();
        // A tolerance window, not exact-equality, since the timestamp
        // column's storage precision can truncate sub-second fractions.
        expect(abs($picture->consent_confirmed_at->diffInSeconds(now())))->toBeLessThan(5);
    });
});

it('still displays and behaves normally for an existing photo with a null consent_confirmed_at', function () {
    // Simulates a photo uploaded before this change existed — never
    // retroactively gated or backfilled, per this feature's own scope.
    $picture = Picture::create([
        'user_id'               => $this->owner->id,
        'planting_location_id'  => $this->location->id,
        'path'                  => 'pictures/legacy.jpg',
        'thumbnail'             => 'pictures/legacy-thumb.jpg',
        'captured_latitude'     => 9.0820,
        'captured_longitude'    => 8.6753,
        'capture_source'        => 'exif',
        'captured_at'           => now(),
        // consent_confirmed_at deliberately left unset (null)
    ]);

    expect($picture->consent_confirmed_at)->toBeNull();

    $response = $this->actingAs($this->owner)
        ->get(route('planting-locations.show', $this->location));

    $response->assertOk();
    $response->assertViewHas('photos', function ($photos) {
        return $photos->count() === 1;
    });
});
