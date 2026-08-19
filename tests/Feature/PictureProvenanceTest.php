<?php

use App\Models\Division;
use App\Models\Picture;
use App\Models\PlantingLocation;
use App\Models\PlantingLocationStatus;
use App\Models\User;
use App\Services\ExifExtractor;
use Database\Seeders\RolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    Storage::fake('public');

    $this->statusPlanned = PlantingLocationStatus::forceCreate(['planting_location_status' => 'Planned']);
    $this->division      = Division::forceCreate(['LGA_name' => 'Test Division']);

    $this->owner = User::factory()->create();
    $this->owner->assignRole('Grower');

    $this->location = PlantingLocation::create([
        'location'    => 'Photo Test Location',
        'division_id' => $this->division->id,
        'status_id'   => $this->statusPlanned->id,
        'user_id'     => $this->owner->id,
    ]);
});

it('records exif provenance on a file-uploaded photo when the ExifExtractor finds usable data', function () {
    $this->instance(ExifExtractor::class, new class extends ExifExtractor {
        public function extract(string $filePath): array
        {
            return [
                'captured_at' => Carbon::parse('2024-03-15 09:30:00'),
                'latitude'    => 12.345678,
                'longitude'   => -56.789012,
            ];
        }
    });

    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($this->owner)
        ->post(route('pictures.upload.store', $this->location), [
            'photos' => [$file],
        ])
        ->assertRedirect(route('planting-locations.show', $this->location));

    $picture = Picture::where('planting_location_id', $this->location->id)->firstOrFail();

    expect($picture->capture_source)->toBe('exif');
    expect($picture->captured_at->format('Y-m-d H:i:s'))->toBe('2024-03-15 09:30:00');
    expect((float) $picture->captured_latitude)->toBe(12.345678);
    expect((float) $picture->captured_longitude)->toBe(-56.789012);
});

it('leaves all provenance fields null when the ExifExtractor finds nothing usable', function () {
    $this->instance(ExifExtractor::class, new class extends ExifExtractor {
        public function extract(string $filePath): array
        {
            return ['captured_at' => null, 'latitude' => null, 'longitude' => null];
        }
    });

    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($this->owner)
        ->post(route('pictures.upload.store', $this->location), [
            'photos' => [$file],
        ])
        ->assertRedirect(route('planting-locations.show', $this->location));

    $picture = Picture::where('planting_location_id', $this->location->id)->firstOrFail();

    expect($picture->capture_source)->toBeNull();
    expect($picture->captured_at)->toBeNull();
    expect($picture->captured_latitude)->toBeNull();
    expect($picture->captured_longitude)->toBeNull();
});

it('records device-geolocation provenance on a camera-captured photo when coordinates are submitted', function () {
    $this->actingAs($this->owner)
        ->post(route('pictures.store'), [
            'image_data'           => 'data:image/jpeg;base64,'.base64_encode('fake-image-bytes'),
            'planting_location_id' => $this->location->id,
            'captured_latitude'    => 3.456789,
            'captured_longitude'   => -12.987654,
        ])
        ->assertRedirect();

    $picture = Picture::where('planting_location_id', $this->location->id)->firstOrFail();

    expect($picture->capture_source)->toBe('device_geolocation');
    expect((float) $picture->captured_latitude)->toBe(3.456789);
    expect((float) $picture->captured_longitude)->toBe(-12.987654);
    expect($picture->captured_at)->not->toBeNull();
});

it('leaves all provenance fields null on a camera-captured photo when no coordinates are submitted', function () {
    $this->actingAs($this->owner)
        ->post(route('pictures.store'), [
            'image_data'           => 'data:image/jpeg;base64,'.base64_encode('fake-image-bytes'),
            'planting_location_id' => $this->location->id,
        ])
        ->assertRedirect();

    $picture = Picture::where('planting_location_id', $this->location->id)->firstOrFail();

    expect($picture->capture_source)->toBeNull();
    expect($picture->captured_at)->toBeNull();
    expect($picture->captured_latitude)->toBeNull();
    expect($picture->captured_longitude)->toBeNull();
});
