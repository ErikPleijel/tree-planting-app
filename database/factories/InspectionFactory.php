<?php

namespace Database\Factories;

use App\Models\Inspection;
use App\Models\User;
use App\Models\PlantingLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    public function definition(): array
    {
    return [
        'inspection_date' => fake()->dateTimeBetween('-1 year', 'now'),
        'comment' => fake()->optional()->sentence(),
        'verified' => fake()->boolean(),
        'user_id' => function () {
            return \App\Models\User::inRandomOrder()->first()->id;
        },
        'planting_location_id' => function () {
            return \App\Models\PlantingLocation::inRandomOrder()->first()->id;
        }
    ];
}
    public function forPlantingLocation(PlantingLocation $plantingLocation): static
    {
        return $this->state(fn (array $attributes) => [
            'planting_location_id' => $plantingLocation->id,
        ]);
    }
}
