<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PlantingLocation;
use App\Models\User;
use App\Models\Division;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlantingLocation>
 */
class PlantingLocationFactory extends Factory
{
    protected $model = PlantingLocation::class;

    public function definition(): array
    {
        return [
            'location' => $this->faker->city(),
            'division_id' => Division::inRandomOrder()->first()?->id ?? Division::factory(),
            'comment' => $this->faker->optional()->sentence(),
            'latitude' => $this->faker->latitude(9.0, 11.0),
            'longitude' => $this->faker->longitude(6.0, 8.0),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'status' => 1, // You can randomize or map this from planting_location_status table
        ];
    }
}
